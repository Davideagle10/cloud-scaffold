import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardLayoutComponent } from './components/dashboard-layout/dashboard-layout.component';
import { ContactCardComponent } from './components/contact-card/contact-card.component';

const routes: Routes = [
  {
    path: '',
    component: DashboardLayoutComponent,
    children: [
      // Other dashboard routes can go here
    ]
  },
  {
    path: 'contact/:id',
    component: ContactCardComponent
  },
  {
    path: 'correct-data',
    loadComponent: () => import('./components/data-correction/data-correction.component').then(m => m.DataCorrectionComponent)
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class DashboardRoutingModule { }