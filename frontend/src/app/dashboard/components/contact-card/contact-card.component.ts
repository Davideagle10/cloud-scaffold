import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { RequestService, RequestDetail } from '../../../core/services/request.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-contact-card',
  templateUrl: './contact-card.component.html',
  styleUrls: ['./contact-card.component.scss'],
  standalone: true,
  imports: [CommonModule]
})
export class ContactCardComponent implements OnInit {
  isLoading = true;
  error: string | null = null;
  request: RequestDetail | null = null;
  requestId: number | null = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private requestService: RequestService
  ) {}

  ngOnInit(): void {
    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.requestId = +idParam; // Convert string to number
      this.loadRequestDetails();
    } else {
      this.error = 'No request ID provided.';
      this.isLoading = false;
    }
  }

  loadRequestDetails(): void {
    if (!this.requestId) return;

    this.isLoading = true;
    this.error = null;
    this.requestService.getRequestDetails(this.requestId).subscribe({
      next: (data) => {
        this.request = data;
        this.isLoading = false;
      },
      error: (err) => {
        this.error = 'Failed to load request details. Please try again later.';
        this.isLoading = false;
        console.error(err);
      }
    });
  }

  updateStatus(contacted: boolean): void {
    if (!this.requestId || this.request?.estado_contacto === contacted) return;

    this.requestService.updateContactStatus(this.requestId, contacted).subscribe({
      next: () => {
        if (this.request) {
          this.request.estado_contacto = contacted;
        }
      },
      error: (err) => {
        // Optionally handle the error in the UI, e.g., show a toast message
        console.error('Failed to update status', err);
      }
    });
  }

  closeCard(): void {
    // Navigate away from the contact card, e.g., back to the main dashboard
    this.router.navigate(['/dashboard']);
  }
}