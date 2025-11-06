import { Component } from '@angular/core';

@Component({
  selector: 'app-landing-page',
  templateUrl: './landing-page.component.html',
  styleUrls: ['./landing-page.component.scss']
})
export class LandingPageComponent {
  // In the future, this data will come from the InfoController endpoint
  title = 'Bienvenido a Nuestra Plataforma de Asesorías de Idiomas';
  description = 'Conectamos a estudiantes con los mejores asesores para un aprendizaje de idiomas efectivo y personalizado. Regístrate para una consulta gratuita.';
}