import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, ReactiveFormsModule } from '@angular/forms';
import { Observable, of } from 'rxjs';
import { catchError, debounceTime, distinctUntilChanged, switchMap, startWith } from 'rxjs/operators';
import { RequestService, AdvisoryRequest, RequestFilters } from '../../../core/services/request.service';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-dashboard-layout',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './dashboard-layout.component.html',
  styleUrls: ['./dashboard-layout.component.scss'],
})
export class DashboardLayoutComponent implements OnInit {
  requests$!: Observable<AdvisoryRequest[]>;
  error: string | null = null;
  filterForm!: FormGroup;
  filtersVisible = true;

  // Available languages for the filter
  availableLanguages = ['Ingles', 'Aleman', 'Portuges', 'Frances'];

  constructor(
    private requestService: RequestService,
    private fb: FormBuilder,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.filterForm = this.fb.group({
      nombres: [''],
      correo: [''],
      idiomas: this.fb.array([]),
      contactado: [null], // Using null for a "tri-state" if needed, or default to false
      fecha_contacto_inicio: [''],
      fecha_contacto_fin: [''],
      fecha_creacion_inicio: [''],
      fecha_creacion_fin: [''],
    });

    this.requests$ = this.filterForm.valueChanges.pipe(
      startWith(this.filterForm.value), // Trigger initial fetch
      debounceTime(300), // Wait for 300ms of silence before triggering
      distinctUntilChanged((prev, curr) => JSON.stringify(prev) === JSON.stringify(curr)),
      switchMap(formValue => {
        // Clean up the filter values before sending
        const filters: RequestFilters = {
          ...formValue,
          contactado: formValue.contactado === null ? undefined : formValue.contactado,
        };
        return this.requestService.getRequests(filters);
      }),
      catchError(err => {
        console.error('Failed to fetch requests', err);
        this.error = 'No se pudieron cargar las solicitudes. Por favor, inténtelo de nuevo más tarde.';
        return of([]); // Return an empty array on error
      })
    );
  }

  get idiomas(): FormArray {
    return this.filterForm.get('idiomas') as FormArray;
  }

  onLanguageToggle(language: string): void {
    const idiomasArray = this.idiomas;
    const index = idiomasArray.value.indexOf(language);

    if (index > -1) {
      idiomasArray.removeAt(index);
    } else {
      idiomasArray.push(this.fb.control(language));
    }
  }

  isLanguageSelected(language: string): boolean {
    return this.idiomas.value.includes(language);
  }

  toggleFilters(): void {
    this.filtersVisible = !this.filtersVisible;
  }

  logout(): void {
    this.authService.logout();
  }
}