<?php

function getMerkKendaraanList() {
    return ['Suzuki', 'Honda', 'Yamaha', 'Kawasaki'];
}

function renderMerkOptions($selected = '', $includeSemua = false) {
    if ($includeSemua) {
        $sel = ($selected === '' || $selected === 'semua') ? 'selected' : '';
        echo "<option value=\"\" $sel>-- Semua Merk --</option>";
    }
    foreach (getMerkKendaraanList() as $merk) {
        $sel = ($selected === $merk) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($merk) . "\" $sel>" . htmlspecialchars($merk) . '</option>';
    }
}

function isValidMerk($merk) {
    return in_array($merk, getMerkKendaraanList(), true);
}
