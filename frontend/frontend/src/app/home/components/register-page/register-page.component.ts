import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-register-page',
  templateUrl: './register-page.component.html',
  styleUrls: ['./register-page.component.scss'],
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule]
})
export class RegisterPageComponent implements OnInit {
  form!: FormGroup;
  cursos: string[] = ['Ingles', 'Franses', 'Chino', 'Portugues', 'Aleman'];

  constructor(
    private fb: FormBuilder,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      name: ['', [Validators.required]],
      phone: ['', [Validators.required]],
      cursos: this.fb.array([], [Validators.required])
    });
  }

  toggleCurso(curso: string): void {
    const cursosArray = this.form.get('cursos') as FormArray;
    if (cursosArray.value.includes(curso)) {
      cursosArray.removeAt(cursosArray.value.findIndex((c: string) => c === curso));
    } else {
      cursosArray.push(this.fb.control(curso));
    }
  }

  onSubmit(): void {
    if (this.form.valid) {
      console.log(this.form.value);
      // Here you would typically send the data to a server
    }
  }

  close(): void {
    this.router.navigate(['/']); // Navigate to home or any other route
  }
}
