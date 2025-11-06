import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { RequestService } from '../../../core/services/request.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-data-correction',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './data-correction.component.html',
  styleUrls: ['./data-correction.component.scss']
})
export class DataCorrectionComponent implements OnInit {
  correctionForm!: FormGroup;
  mockRequestId = 42;
  mockRequestData: { [key: string]: string } = {
    email: 'ejemplo@ejp.co',
    telefono: '123456789',
    nombre: 'Juan Pérez'
  };

  constructor(
    private fb: FormBuilder,
    private router: Router,
    private requestService: RequestService
  ) { }

  ngOnInit(): void {
    this.correctionForm = this.fb.group({
      campoACorregir: ['', Validators.required],
      valorAnterior: [{ value: '', disabled: true }],
      valorNuevo: ['', Validators.required]
    });

    this.correctionForm.get('campoACorregir')!.valueChanges.subscribe(field => {
      const valorAnteriorControl = this.correctionForm.get('valorAnterior');
      const valorNuevoControl = this.correctionForm.get('valorNuevo');

      if (field) {
        const key = this.getMappedField(field);
        valorAnteriorControl!.setValue(this.mockRequestData[key]);
        valorNuevoControl!.enable();
      } else {
        valorAnteriorControl!.setValue('');
        valorNuevoControl!.disable();
      }
    });

    this.correctionForm.get('valorNuevo')!.disable();
  }

  getMappedField(field: string): string {
    switch (field) {
      case 'Correo':
        return 'correo';
      case 'Teléfono':
        return 'telefono';
      case 'Nombre':
        return 'nombre';
      default:
        return '';
    }
  }

  onSubmit(): void {
    if (this.correctionForm.valid) {
      const formValue = this.correctionForm.getRawValue();
      const payload = {
        requestId: this.mockRequestId,
        campoACorregir: this.getMappedField(formValue.campoACorregir),
        valorAnterior: formValue.valorAnterior,
        valorNuevo: formValue.valorNuevo
      };

      this.requestService.correctData(payload).subscribe({
        next: () => {
          console.log('Data corrected successfully');
          this.close();
        },
        error: (err) => console.error('Error correcting data', err)
      });
    }
  }

  close(): void {
    this.router.navigate(['/dashboard']); // Navigate to a safe route
  }
}