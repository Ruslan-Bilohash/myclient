<?php
declare(strict_types=1);

/**
 * Flat-file JSON storage. Every collection lives in data/{name}.json as {"items": [...], "seq": N}.
 * Swapping this for MySQL later only means rewriting mk_store_load()/mk_store_save() —
 * every caller only ever deals with plain PHP arrays.
 */

function mk_store_path(string $name): string
{
    return MK_DATA_DIR . '/' . preg_replace('/[^a-z0-9_-]/', '', $name) . '.json';
}

/** @return array{items: list<array<string,mixed>>, seq: int} */
function mk_store_load(string $name): array
{
    $path = mk_store_path($name);
    if (!is_file($path)) {
        return ['items' => [], 'seq' => 0];
    }
    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return ['items' => [], 'seq' => 0];
    }
    flock($fh, LOCK_SH);
    $raw = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);

    $data = json_decode((string) $raw, true);
    if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
        return ['items' => [], 'seq' => 0];
    }

    return ['items' => array_values($data['items']), 'seq' => (int) ($data['seq'] ?? 0)];
}

/** @param array{items: list<array<string,mixed>>, seq?: int} $data */
function mk_store_save(string $name, array $data): bool
{
    $path = mk_store_path($name);
    $payload = json_encode(
        ['items' => array_values($data['items']), 'seq' => (int) ($data['seq'] ?? 0)],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($payload === false) {
        return false;
    }

    $tmp = $path . '.tmp' . bin2hex(random_bytes(4));
    if (file_put_contents($tmp, $payload, LOCK_EX) === false) {
        return false;
    }

    return rename($tmp, $path);
}

/** Read-modify-write a collection under an exclusive lock to avoid lost updates. */
function mk_store_transact(string $name, callable $mutator): bool
{
    $path = mk_store_path($name);
    $fh = fopen($path, 'c+b');
    if ($fh === false) {
        return false;
    }
    flock($fh, LOCK_EX);
    $raw = stream_get_contents($fh);
    $data = json_decode((string) $raw, true);
    if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
        $data = ['items' => [], 'seq' => 0];
    } else {
        $data['items'] = array_values($data['items']);
        $data['seq'] = (int) ($data['seq'] ?? 0);
    }

    $result = $mutator($data);
    if (is_array($result)) {
        $data = $result;
    }

    $payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload !== false) {
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, $payload);
        fflush($fh);
    }
    flock($fh, LOCK_UN);
    fclose($fh);

    return $payload !== false;
}

function mk_store_next_seq(string $name): int
{
    $seq = 0;
    mk_store_transact($name, function (array $data) use (&$seq): array {
        $data['seq'] = (int) ($data['seq'] ?? 0) + 1;
        $seq = $data['seq'];

        return $data;
    });

    return $seq;
}

/** @return array<string,mixed>|null */
function mk_store_find(string $name, string $id): ?array
{
    $data = mk_store_load($name);
    foreach ($data['items'] as $item) {
        if (($item['id'] ?? null) === $id) {
            return $item;
        }
    }

    return null;
}

/** @return list<array<string,mixed>> */
function mk_store_all(string $name): array
{
    return mk_store_load($name)['items'];
}

/** @param array<string,mixed> $row */
function mk_store_insert(string $name, array $row): array
{
    mk_store_transact($name, function (array $data) use ($row): array {
        $data['items'][] = $row;

        return $data;
    });

    return $row;
}

/** @param array<string,mixed> $patch */
function mk_store_update(string $name, string $id, array $patch): bool
{
    $found = false;
    mk_store_transact($name, function (array $data) use ($id, $patch, &$found): array {
        foreach ($data['items'] as $i => $item) {
            if (($item['id'] ?? null) === $id) {
                $data['items'][$i] = array_replace($item, $patch);
                $found = true;
                break;
            }
        }

        return $data;
    });

    return $found;
}

function mk_store_delete(string $name, string $id): bool
{
    $found = false;
    mk_store_transact($name, function (array $data) use ($id, &$found): array {
        $data['items'] = array_values(array_filter($data['items'], function (array $item) use ($id, &$found): bool {
            if (($item['id'] ?? null) === $id) {
                $found = true;

                return false;
            }

            return true;
        }));

        return $data;
    });

    return $found;
}
