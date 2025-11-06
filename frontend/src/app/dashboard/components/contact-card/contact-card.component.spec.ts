import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { ActivatedRoute } from '@angular/router';
import { of, throwError } from 'rxjs';

import { ContactCardComponent } from './contact-card.component';
import { RequestService } from '../../../core/services/request.service';

describe('ContactCardComponent', () => {
  let component: ContactCardComponent;
  let fixture: ComponentFixture<ContactCardComponent>;
  let requestService: RequestService;

  const mockRequestDetail = {
    id: 42,
    nombre: 'Test User',
    email: 'test@example.com',
    telefono: '1234567890',
    idiomas: ['English', 'Spanish'],
    estado_contacto: false,
    fecha_creacion: new Date().toISOString()
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [
        HttpClientTestingModule,
        RouterTestingModule,
        ContactCardComponent // Import standalone component
      ],
      providers: [
        RequestService,
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              paramMap: {
                get: (key: string) => '42',
              },
            },
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(ContactCardComponent);
    component = fixture.componentInstance;
    requestService = TestBed.inject(RequestService);
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should load request details on init', () => {
    spyOn(requestService, 'getRequestDetails').and.returnValue(of(mockRequestDetail));
    fixture.detectChanges(); // ngOnInit()
    expect(component.isLoading).toBe(false);
    expect(component.request).toEqual(mockRequestDetail);
    expect(requestService.getRequestDetails).toHaveBeenCalledWith(42);
  });

  it('should handle error when loading request details', () => {
    spyOn(requestService, 'getRequestDetails').and.returnValue(throwError(() => new Error('Failed to load')));
    fixture.detectChanges(); // ngOnInit()
    expect(component.isLoading).toBe(false);
    expect(component.error).not.toBeNull();
    expect(component.request).toBeNull();
  });

  it('should update contact status', () => {
    spyOn(requestService, 'updateContactStatus').and.returnValue(of({}));
    component.request = { ...mockRequestDetail };
    component.requestId = 42;

    component.updateStatus(true);

    expect(requestService.updateContactStatus).toHaveBeenCalledWith(42, true);
    expect(component.request.estado_contacto).toBe(true);
  });
});