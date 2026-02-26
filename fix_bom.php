<?php
$file = '.env';
$contents = file_get_contents($file);
if (substr($contents, 0, 3) === "\xef\xbb\xbf") {
    file_put_contents($file, substr($contents, 3));
    echo "BOM removed from $file\n";
} else {
    echo "No BOM found in $file\n";
}
