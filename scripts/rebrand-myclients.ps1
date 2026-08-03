# Rebrand product name: My Sites -> My Clients (and locale equivalents)
$path = 'C:\bilohash\myclient\index.php'
$utf8 = New-Object System.Text.UTF8Encoding $false
$c = [IO.File]::ReadAllText($path, $utf8)

# Build Ukrainian/Russian/Norwegian strings via codepoints to avoid file encoding issues
function U([int[]]$codes) {
  -join ($codes | ForEach-Object { [char]$_ })
}

# Мої сайти  /  Мої клієнти
$ukOld = U @(0x041C,0x043E,0x0457,0x0020,0x0441,0x0430,0x0439,0x0442,0x0438)
$ukNew = U @(0x041C,0x043E,0x0457,0x0020,0x043A,0x043B,0x0456,0x0454,0x043D,0x0442,0x0438)
# Мои сайты  /  Мои клиенты
$ruOld = U @(0x041C,0x043E,0x0438,0x0020,0x0441,0x0430,0x0439,0x0442,0x044B)
$ruNew = U @(0x041C,0x043E,0x0438,0x0020,0x043A,0x043B,0x0438,0x0435,0x043D,0x0442,0x044B)

$c = $c.Replace('My Sites', 'My Clients')
$c = $c.Replace('my sites', 'my clients')
$c = $c.Replace('Mine nettsteder', 'Mine kunder')
$c = $c.Replace('Mina webbplatser', 'Mina kunder')
$c = $c.Replace('Moje strony', 'Moi klienci')
$c = $c.Replace($ukOld, $ukNew)
$c = $c.Replace($ruOld, $ruNew)

# Collapse accidental doubles
$c = $c.Replace('My Clients · My Clients', 'My Clients')
$c = $c.Replace('My Clients / My Clients', 'My Clients')
$c = $c.Replace('My Clients + My Clients', 'My Clients')
$c = $c.Replace('My Clients · My Clients', 'My Clients')
$c = $c.Replace($ukNew + ' · ' + $ukNew, $ukNew)
$c = $c.Replace($ruNew + ' · ' + $ruNew, $ruNew)
$c = $c.Replace('Mine kunder · Mine kunder', 'Mine kunder')
$c = $c.Replace('Mine kunder + Mine kunder', 'Mine kunder')
$c = $c.Replace('Mine kunder / Mine kunder', 'Mine kunder')

[IO.File]::WriteAllText($path, $c, $utf8)
$left = ([regex]::Matches($c, 'My Sites')).Count
Write-Output "saved left_MySites=$left MyClients=$(([regex]::Matches($c,'My Clients')).Count)"
