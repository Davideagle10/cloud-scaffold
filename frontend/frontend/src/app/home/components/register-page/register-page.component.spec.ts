import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { RegisterPageComponent } from './register-page.component';

describe('RegisterPageComponent', () => {
  let component: RegisterPageComponent;
  let fixture: ComponentFixture<RegisterPageComponent>;
  let router: Router;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ReactiveFormsModule, RegisterPageComponent],
      providers: [
        {
          provide: Router,
          useValue: {
            navigate: jasmine.createSpy('navigate'),
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(RegisterPageComponent);
    component = fixture.componentInstance;
    router = TestBed.inject(Router);
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should initialize the form with empty values', () => {
    expect(component.form.value).toEqual({
      email: '',
      name: '',
      phone: '',
      cursos: [],
    });
  });

  it('should make the email field required and validate email format', () => {
    const emailControl = component.form.get('email');
    expect(emailControl?.valid).toBeFalsy();

    emailControl?.setValue('test');
    expect(emailControl?.valid).toBeFalsy();

    emailControl?.setValue('test@example.com');
    expect(emailControl?.valid).toBeTruthy();
  });

  it('should make the name field required', () => {
    const nameControl = component.form.get('name');
    expect(nameControl?.valid).toBeFalsy();

    nameControl?.setValue('John Doe');
    expect(nameControl?.valid).toBeTruthy();
  });

  it('should make the phone field required', () => {
    const phoneControl = component.form.get('phone');
    expect(phoneControl?.valid).toBeFalsy();

    phoneControl?.setValue('1234567890');
    expect(phoneControl?.valid).toBeTruthy();
  });

  it('should toggle courses correctly', () => {
    const cursosControl = component.form.get('cursos');
    expect(cursosControl?.value.length).toBe(0);

    component.toggleCurso('Ingles');
    expect(cursosControl?.value.length).toBe(1);
    expect(cursosControl?.value).toContain('Ingles');

    component.toggleCurso('Franses');
    expect(cursosControl?.value.length).toBe(2);
    expect(cursosControl?.value).toContain('Franses');

    component.toggleCurso('Ingles');
    expect(cursosControl?.value.length).toBe(1);
    expect(cursosControl?.value).not.toContain('Ingles');
  });

  it('should call router.navigate when close is called', () => {
    component.close();
    expect(router.navigate).toHaveBeenCalledWith(['/']);
  });
});
