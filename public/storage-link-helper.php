<?php
/**
 * Asistente para crear el enlace simbólico de almacenamiento (storage:link) en cPanel
 *
 * USO:
 * 1. Sube tu proyecto a cPanel.
 * 2. Visita en tu navegador: https://api.tudominio.com/storage-link-helper.php
 * 3. Una vez creado el enlace con éxito, ELIMINA este archivo del servidor por seguridad.
 */

$target = dirname(__DIR__) . '/storage/app/public';
$shortcut = __DIR__ . '/storage';

echo '<h2>BaiFa - Asistente de Enlace de Almacenamiento (cPanel)</h2>';

if (file_exists($shortcut)) {
    echo '<p style="color: green;">✔ El enlace o carpeta <strong>' . htmlspecialchars($shortcut) . '</strong> ya existe.</p>';
} else {
    if (!file_exists($target)) {
        mkdir($target, 0755, true);
    }

    if (symlink($target, $shortcut)) {
        echo '<p style="color: green;">✔ Enlace simbólico creado exitosamente:</p>';
        echo '<ul>';
        echo '<li>Origen: ' . htmlspecialchars($target) . '</li>';
        echo '<li>Destino: ' . htmlspecialchars($shortcut) . '</li>';
        echo '</ul>';
    } else {
        echo '<p style="color: red;">✖ No fue posible crear el symlink automáticamente. Si tu servidor no permite symlinks, crea un acceso directo o alias en cPanel.</p>';
    }
}

echo '<p><strong>IMPORTANTE:</strong> Por motivos de seguridad, borra este archivo <code>storage-link-helper.php</code> una vez finalizado.</p>';
