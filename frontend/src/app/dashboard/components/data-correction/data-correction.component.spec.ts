import { ComponentFixture, TestBed, fakeAsync, tick } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RouterTestingModule } from '@angular/router/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, throwError } from 'rxjs';

import { DataCorrectionComponent } from './data-correction.component';
import { RequestService } from '../../../core/services/request.service';

describe('DataCorrectionComponent', () => {
  let component: DataCorrectionComponent;
  let fixture: ComponentFixture<DataCorrectionComponent>;
  let requestService: RequestService;
  let router: Router;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [
        DataCorrectionComponent, // Import the standalone component
        RouterTestingModule.withRoutes([]), // Provide routes for Router
        HttpClientTestingModule
      ],
      providers: [ RequestService ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(DataCorrectionComponent);
    component = fixture.componentInstance;
    requestService = TestBed.inject(RequestService); // Inject the service
    router = TestBed.inject(Router); // Inject the router
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize the form correctly', () => {
    expect(component.correctionForm).toBeDefined();
    expect(component.correctionForm.get('campoACorregir')).toBeDefined();
    expect(component.correctionForm.get('valorAnterior')).toBeDefined();
    expect(component.correctionForm.get('valorNuevo')).toBeDefined();
    expect(component.correctionForm.get('valorAnterior')!.disabled).toBeTrue();
    expect(component.correctionForm.get('valorNuevo')!.disabled).toBeTrue();
  });

  it('should update valorAnterior and enable valorNuevo when a field is selected', () => {
    const campoACorregirControl = component.correctionForm.get('campoACorregir');
    const valorAnteriorControl = component.correctionForm.get('valorAnterior');
    const valorNuevoControl = component.correctionForm.get('valorNuevo');

    campoACorregirControl!.setValue('Correo');
    fixture.detectChanges();

    expect(valorAnteriorControl!.value).toBe('ejemplo@ejp.co');
    expect(valorNuevoControl!.enabled).toBeTrue();
  });

  it('should clear valorAnterior and disable valorNuevo when selection is cleared', () => {
    const campoACorregirControl = component.correctionForm.get('campoACorregir');
    const valorAnteriorControl = component.correctionForm.get('valorAnterior');
    const valorNuevoControl = component.correctionForm.get('valorNuevo');

    campoACorregirControl!.setValue('Correo');
    fixture.detectChanges();

    campoACorregirControl!.setValue('');
    fixture.detectChanges();

    expect(valorAnteriorControl!.value).toBe('');
    expect(valorNuevoControl!.disabled).toBeTrue();
  });

  it('should not submit if the form is invalid', () => {
    spyOn(requestService, 'correctData').and.callThrough();
    component.onSubmit();
    expect(requestService.correctData).not.toHaveBeenCalled();
  });

  it('should call correctData on valid form submission and navigate on success', fakeAsync(() => {
    spyOn(requestService, 'correctData').and.returnValue(of({ success: true }));
    spyOn(router, 'navigate').and.stub();

    component.correctionForm.get('campoACorregir')!.setValue('Correo');
    component.correctionForm.get('valorNuevo')!.setValue('new.email@example.com');
    fixture.detectChanges();

    component.onSubmit();
    tick(); // Wait for async operations

    const expectedPayload = {
      requestId: 42,
      campoACorregir: 'email',
      valorAnterior: 'ejemplo@ejp.co',
      valorNuevo: 'new.email@example.com'
    };

    expect(requestService.correctData).toHaveBeenCalledWith(expectedPayload);
    expect(router.navigate).toHaveBeenCalledWith(['/dashboard']);
  }));

  it('should log an error on submission failure', fakeAsync(() => {
    spyOn(requestService, 'correctData').and.returnValue(throwError(() => new Error('Service Error')));
    spyOn(console, 'error');

    component.correctionForm.get('campoACorregir')!.setValue('Teléfono');
    component.correctionForm.get('valorNuevo')!.setValue('987654321');
    fixture.detectChanges();

    component.onSubmit();
    tick();

    expect(requestService.correctData).toHaveBeenCalled();
    expect(console.error).toHaveBeenCalledWith('Error correcting data', jasmine.any(Error));
  }));

  it('should navigate to dashboard on close()', () => {
    spyOn(router, 'navigate').and.stub();
    component.close();
    expect(router.navigate).toHaveBeenCalledWith(['/dashboard']);
  });
});