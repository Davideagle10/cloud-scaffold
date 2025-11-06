import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup = new FormGroup({});
  isSubmitting = false;
  loginError: string | null = null;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.loginForm = this.fb.group({
      usuario: ['', [Validators.required]],
      password: ['', [Validators.required]]
    });
  }

  onSubmit(): void {
    if (this.loginForm.invalid || this.isSubmitting) {
      return;
    }

    this.isSubmitting = true;
    this.loginError = null;

    const credentials = { ...this.loginForm.value };
    const sharedSecret = 'my-super-secret-key'; // As requested, a shared key.

    // Encode the password using Base64 with the shared secret
    try {
      credentials.password = btoa(`${sharedSecret}:${credentials.password}`);
    } catch (e) {
      console.error('Failed to encode password:', e);
      this.loginError = 'An unexpected error occurred during login.';
      this.isSubmitting = false;
      return;
    }

    this.authService.login(credentials).subscribe({
      next: (response) => {
        console.log('Login successful!', response);
        // In a real app, we would save the auth token here
        this.router.navigate(['/dashboard']); // Redirect to dashboard on success
      },
      error: (error) => {
        console.error('Login failed:', error);
        this.loginError = 'Invalid credentials';
        this.isSubmitting = false;
      }
    });
  }
}