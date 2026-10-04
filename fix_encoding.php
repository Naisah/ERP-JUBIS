<?php
$files = [
    "resources/js/Pages/Cart.vue", 
    "resources/js/Pages/Admin/Invoices/Show.vue", 
    "resources/js/Pages/Admin/Quotes/Show.vue"
];
foreach($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Look for any 3-byte or garbled character before {{ and replace with ₱
        $content = preg_replace('/[^\x20-\x7E\t\r\n]+\{\{/', '₱{{', $content);
        file_put_contents($file, $content);
    }
}
echo "Done\n";
