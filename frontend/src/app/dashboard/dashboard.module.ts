import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { DashboardLayoutComponent } from './components/dashboard-layout/dashboard-layout.component';
import { ContactCardComponent } from './components/contact-card/contact-card.component';


@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    DashboardRoutingModule,
    DashboardLayoutComponent,
    ContactCardComponent
  ]
})
export class DashboardModule { }