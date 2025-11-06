import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { of, throwError } from 'rxjs';
import { AuthService } from '../../../core/services/auth.service';
import { LoginComponent } from './login.component';
import { CommonModule } from '@angular/common';
import { HttpClientTestingModule } from '@angular/common/http/testing';

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let authServiceMock: jasmine.SpyObj<AuthService>;
  let routerMock: jasmine.SpyObj<Router>;

  beforeEach(async () => {
    // Create spy objects for the services
    authServiceMock = jasmine.createSpyObj('AuthService', ['login']);
    routerMock = jasmine.createSpyObj('Router', ['navigate']);

    await TestBed.configureTestingModule({
      imports: [
        CommonModule,
        ReactiveFormsModule,
        HttpClientTestingModule, // Provides mocks for HttpClient if needed by services
        LoginComponent, // Import the standalone component
      ],
      providers: [
        // Provide the mock services
        { provide: AuthService, useValue: authServiceMock },
        { provide: Router, useValue: routerMock },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
    fixture.detectChanges(); // This triggers ngOnInit
  });

  it('should create the component', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize the login form with usuario and password controls', () => {
    expect(component.loginForm).toBeDefined();
    expect(component.loginForm.get('usuario')).toBeDefined();
    expect(component.loginForm.get('password')).toBeDefined();
  });

  it('should make the form invalid when fields are empty', () => {
    expect(component.loginForm.valid).toBeFalsy();
  });

  it('should make the form valid when fields are filled', () => {
    component.loginForm.setValue({ usuario: 'testuser', password: 'password123' });
    expect(component.loginForm.valid).toBeTruthy();
  });

  it('should not call authService.login if the form is invalid on submit', () => {
    component.onSubmit();
    expect(authServiceMock.login).not.toHaveBeenCalled();
  });

  describe('on successful login', () => {
    beforeEach(() => {
      // Setup the mock to return a successful response
      authServiceMock.login.and.returnValue(of({ success: true, token: 'fake-token' }));

      // Fill out the form and submit
      component.loginForm.setValue({ usuario: 'testuser', password: 'password123' });
      component.onSubmit();
    });

    it('should call authService.login with the form values', () => {
      expect(authServiceMock.login).toHaveBeenCalledWith({ usuario: 'testuser', password: 'password123' });
    });

    it('should navigate to the /dashboard route', () => {
      expect(routerMock.navigate).toHaveBeenCalledWith(['/dashboard']);
    });

    it('should not set an error message', () => {
      expect(component.loginError).toBeNull();
    });
  });

  describe('on failed login', () => {
    beforeEach(() => {
      // Setup the mock to return an error response
      authServiceMock.login.and.returnValue(throwError(() => ({ error: 'Invalid credentials' })));

      // Fill out the form and submit
      component.loginForm.setValue({ usuario: 'wronguser', password: 'wrongpassword' });
      component.onSubmit();
    });

    it('should call authService.login', () => {
      expect(authServiceMock.login).toHaveBeenCalledWith({ usuario: 'wronguser', password: 'wrongpassword' });
    });

    it('should set the loginError message', () => {
      expect(component.loginError).toBe('Invalid credentials');
    });

    it('should not navigate to another route', () => {
      expect(routerMock.navigate).not.toHaveBeenCalled();
    });

    it('should reset the isSubmitting flag', () => {
      expect(component.isSubmitting).toBeFalse();
    });
  });
});