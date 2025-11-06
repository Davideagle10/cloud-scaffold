import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { RequestService, RequestSummary, AdvisoryRequest, RequestFilters } from './request.service';
import { environment } from '../../../environments/environment';

describe('RequestService', () => {
  let service: RequestService;
  let httpMock: HttpTestingController;
  const apiUrl = `${environment.API_BASE_URL}/v1/requests`;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [RequestService]
    });
    service = TestBed.inject(RequestService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  describe('getRequests', () => {
    it('should fetch requests with no filters', () => {
      const mockRequests: AdvisoryRequest[] = [
        { id: 1, nombres: 'John Doe', correo: 'john@example.com', idiomas: [], contactado: false, fecha_creacion: '', fecha_contacto: '' }
      ];

      service.getRequests().subscribe(requests => {
        expect(requests.length).toBe(1);
        expect(requests).toEqual(mockRequests);
      });

      const req = httpMock.expectOne(apiUrl);
      expect(req.request.method).toBe('GET');
      req.flush(mockRequests);
    });

    it('should fetch requests with filters', () => {
      const filters: RequestFilters = { nombres: 'Jane' };
      const mockRequests: AdvisoryRequest[] = [
        { id: 2, nombres: 'Jane Doe', correo: 'jane@example.com', idiomas: [], contactado: false, fecha_creacion: '', fecha_contacto: '' }
      ];

      service.getRequests(filters).subscribe(requests => {
        expect(requests).toEqual(mockRequests);
      });

      const req = httpMock.expectOne(`${apiUrl}?nombres=Jane`);
      expect(req.request.method).toBe('GET');
      req.flush(mockRequests);
    });
  });

  describe('getRequestSummary', () => {
    it('should fetch a request summary', () => {
      const mockSummary: RequestSummary = {
        nombreSolicitante: 'John Doe',
        estado: 'Pending',
        idiomasSolicitados: ['English'],
        requestId: 1
      };
      const requestId = 1;

      service.getRequestSummary(requestId).subscribe(summary => {
        expect(summary).toEqual(mockSummary);
      });

      const req = httpMock.expectOne(`${apiUrl}/summary/${requestId}`);
      expect(req.request.method).toBe('GET');
      req.flush(mockSummary);
    });
  });
});