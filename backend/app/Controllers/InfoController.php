<?php

namespace App\Controllers;

class InfoController
{
    /**
     * Provides static information for the landing page.
     * This simulates an endpoint that might deliver general content.
     */
    public function getLandingInfo(): void
    {
        http_response_code(200);
        echo json_encode([
            'title' => 'Bienvenido a Nuestra Plataforma de Asesorías de Idiomas',
            'description' => 'Conectamos a estudiantes con los mejores asesores para un aprendizaje de idiomas efectivo y personalizado. Regístrate para una consulta gratuita.',
            'contact' => [
                'email' => 'info@languageadvisors.com',
                'phone' => '+1 (555) 123-4567',
            ],
            'features' => [
                'Asesores expertos',
                'Horarios flexibles',
                'Planes personalizados',
                'Soporte 24/7'
            ]
        ]);
    }
}