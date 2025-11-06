import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { of } from 'rxjs';

import { DashboardLayoutComponent } from './dashboard-layout.component';
import { RequestService } from '../../../core/services/request.service';

describe('DashboardLayoutComponent', () => {
  let component: DashboardLayoutComponent;
  let fixture: ComponentFixture<DashboardLayoutComponent>;
  let requestService: RequestService;

  const mockRequests = [
    { id: 1, nombres: 'Test User', correo: 'test@example.com', idiomas: ['Ingles'], contactado: false, fecha_creacion: '2023-01-01', fecha_contacto: '' },
  ];

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [
        ReactiveFormsModule,
        HttpClientTestingModule,
        DashboardLayoutComponent // Import standalone component
      ],
      providers: [RequestService],
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardLayoutComponent);
    component = fixture.componentInstance;
    requestService = TestBed.inject(RequestService);

    // Spy on the service method before fixture.detectChanges() to catch initial calls
    spyOn(requestService, 'getRequests').and.returnValue(of(mockRequests));

    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize the filter form on ngOnInit', () => {
    expect(component.filterForm).toBeDefined();
    expect(component.filterForm.get('nombres')).toBeDefined();
    expect(component.filterForm.get('correo')).toBeDefined();
    expect(component.filterForm.get('idiomas')).toBeDefined();
    expect(component.filterForm.get('contactado')).toBeDefined();
  });

  it('should call getRequests on form value changes', fakeAsync(() => {
    component.filterForm.get('nombres')?.setValue('John Doe');
    tick(300); // Wait for debounceTime

    expect(requestService.getRequests).toHaveBeenCalled();
  }));

  it('should add a language to the form array when onLanguageToggle is called', () => {
    const language = 'Aleman';
    component.onLanguageToggle(language);
    expect(component.idiomas.value).toContain(language);
  });

  it('should remove a language from the form array when onLanguageToggle is called twice', () => {
    const language = 'Frances';
    component.onLanguageToggle(language); // Add
    component.onLanguageToggle(language); // Remove
    expect(component.idiomas.value).not.toContain(language);
  });

  it('should toggle the filtersVisible property when toggleFilters is called', () => {
    expect(component.filtersVisible).toBe(true);
    component.toggleFilters();
    expect(component.filtersVisible).toBe(false);
    component.toggleFilters();
    expect(component.filtersVisible).toBe(true);
  });

  it('should call requestService with correct parameters when form changes', fakeAsync(() => {
    const filters = {
      nombres: 'Jane',
      correo: 'jane@example.com',
      idiomas: ['Ingles'],
      contactado: true,
      fecha_contacto_inicio: '2023-01-01',
      fecha_contacto_fin: '2023-01-31',
      fecha_creacion_inicio: '2022-01-01',
      fecha_creacion_fin: '2022-12-31',
    };

    // Set value for each control individually to trigger valueChanges
    Object.keys(filters).forEach(key => {
        if (key === 'idiomas') {
            component.onLanguageToggle(filters.idiomas[0]);
        } else {
            component.filterForm.get(key)?.setValue((filters as any)[key]);
        }
    });

    tick(300); // Debounce time

    const expectedFilters = {
        ...filters,
        // The form sends the full object, so we verify the service is called with it
    };

    // The spy is called with a cleaned-up version of the form value
    const lastCallArgs = (requestService.getRequests as jasmine.Spy).calls.mostRecent().args[0];

    expect(lastCallArgs.nombres).toBe(filters.nombres);
    expect(lastCallArgs.correo).toBe(filters.correo);
    expect(lastCallArgs.idiomas).toEqual(filters.idiomas);
    expect(lastCallArgs.contactado).toBe(filters.contactado);
  }));
});