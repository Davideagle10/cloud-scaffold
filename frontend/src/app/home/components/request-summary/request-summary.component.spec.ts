import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { RouterTestingModule } from '@angular/router/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, throwError } from 'rxjs';
import { ActivatedRoute } from '@angular/router';

import { RequestSummaryComponent } from './request-summary.component';
import { RequestService, RequestSummary } from '../../../core/services/request.service';

describe('RequestSummaryComponent', () => {
  let component: RequestSummaryComponent;
  let fixture: ComponentFixture<RequestSummaryComponent>;
  let requestService: RequestService;
  let route: ActivatedRoute;

  const mockSummary: RequestSummary = {
    nombreSolicitante: 'John Doe',
    estado: 'Pending',
    idiomasSolicitados: ['English', 'Spanish'],
    requestId: 1
  };

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      imports: [
        HttpClientTestingModule,
        RouterTestingModule,
        RequestSummaryComponent
      ],
      providers: [
        RequestService,
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              paramMap: {
                get: (key: string) => '1'
              }
            }
          }
        }
      ]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RequestSummaryComponent);
    component = fixture.componentInstance;
    requestService = TestBed.inject(RequestService);
    route = TestBed.inject(ActivatedRoute);
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should fetch summary on init and display data', () => {
    spyOn(requestService, 'getRequestSummary').and.returnValue(of(mockSummary));

    fixture.detectChanges();

    expect(component.isLoading).toBe(false);
    expect(component.hasError).toBe(false);
    expect(component.summary).toEqual(mockSummary);

    const compiled = fixture.nativeElement;
    expect(compiled.querySelector('h2').textContent).toContain(mockSummary.nombreSolicitante);
    expect(compiled.querySelectorAll('li').length).toBe(2);
  });

  it('should handle error when fetching summary', () => {
    spyOn(requestService, 'getRequestSummary').and.returnValue(throwError(() => new Error('Error')));

    fixture.detectChanges();

    expect(component.isLoading).toBe(false);
    expect(component.hasError).toBe(true);
    expect(component.summary).toBeNull();
  });

  it('should handle missing id in route', () => {
    (route.snapshot.paramMap.get as jasmine.Spy).and.returnValue(null);
    spyOn(requestService, 'getRequestSummary');

    fixture.detectChanges();

    expect(component.isLoading).toBe(false);
    expect(component.hasError).toBe(true);
    expect(requestService.getRequestSummary).not.toHaveBeenCalled();
  });
});