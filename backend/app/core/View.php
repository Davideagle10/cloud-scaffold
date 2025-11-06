<?php

class View
{
    /**
     * Renderiza una vista completa reemplazando placeholders con datos.
     *
     * @param string $contentView La ruta de la vista de contenido (ej. 'auth/login_form').
     * @param array $data Un array asociativo de datos para reemplazar en la vista.
     */
    public static function render(string $contentView, array $data = [])
    {
        // Definir rutas a los archivos de plantilla
        $basePath = __DIR__ . '/../../static/views/';
        $headerPath = $basePath . 'templates/header.html';
        $footerPath = $basePath . 'templates/footer.html';
        $contentPath = $basePath . $contentView . '.html';

        // Leer el contenido de las plantillas
        $header = file_exists($headerPath) ? file_get_contents($headerPath) : 'Header no encontrado.';
        $footer = file_exists($footerPath) ? file_get_contents($footerPath) : 'Footer no encontrado.';
        $content = file_exists($contentPath) ? file_get_contents($contentPath) : 'Contenido no encontrado.';

        // Ensamblar la página completa
        $fullPage = $header . $content . $footer;

        // Añadir datos por defecto que no vienen del controlador
        $data['current_year'] = date('Y');

        // Reemplazar todos los placeholders con los datos proporcionados
        foreach ($data as $key => $value) {
            $fullPage = str_replace('{{' . $key . '}}', $value, $fullPage);
        }

        // Limpiar cualquier placeholder que no haya sido reemplazado para evitar mostrarlos
        $fullPage = preg_replace('/\{\{.*?\}\}/', '', $fullPage);

        // Imprimir la página renderizada
        echo $fullPage;
    }
}