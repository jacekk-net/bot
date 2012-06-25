# Numery E
REMOVEGLOB /data/e/*
REMOVEDIR /data/e

# GIT
REMOVEGLOB /.git/*
REMOVEDIR /.git
REMOVE /cache/.emptydir
REMOVE /data/humor/archiwum/.emptydir
REMOVE /data/kino/cache/.emptydir
REMOVE /data/kurs/archiwum/.emptydir
REMOVE /data/lotto/archiwum/.emptydir
REMOVE /data/tv/cache/.emptydir
REMOVE /database/.emptydir

# Moje ustawienia
REPLACEBETWEEN /class/config.php "'numer' => '" ' ""
REPLACEBETWEEN /class/config.php "'login' => '" ' ""
REPLACEBETWEEN /class/config.php "'haslo' => '" ' ""
REPLACEBETWEEN /class/config.php "'user' => '" ' ""
REPLACEBETWEEN /class/config.php "'pass' => '" ' ""
REPLACEBETWEEN /class/config.php "'key' => '" ' ""

# Informacje o autorze
HEADER /BotGG.php "<?php" HEADER
HEADER /BotIMI.php "<?php" HEADER
HEADER /BotHTTP.php "<?php" HEADER
HEADER /class/*.php "<?php" HEADER
HEADER /data/update*.php "<?php" HEADER
HEADER /data/*/parse.php "<?php" HEADER
HEADER /data/*/pobierz.php "<?php" HEADER
HEADER /modules/*.php "<?php" HEADER
REMOVE /HEADER

SETUSER httpd
SETGROUP daemon
CHOWN /
