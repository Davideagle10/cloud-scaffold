import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { RequestService, RequestSummary } from '../../../core/services/request.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-request-summary',
  templateUrl: './request-summary.component.html',
  styleUrls: ['./request-summary.component.scss'],
  standalone: true,
  imports: [CommonModule]
})
export class RequestSummaryComponent implements OnInit {
  public summary: RequestSummary | null = null;
  public isLoading = true;
  public hasError = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private requestService: RequestService
  ) { }

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.requestService.getRequestSummary(+id).subscribe({
        next: (data) => {
          this.summary = data;
          this.isLoading = false;
        },
        error: () => {
          this.hasError = true;
          this.isLoading = false;
        }
      });
    } else {
      // Handle the case where the ID is not present
      this.hasError = true;
      this.isLoading = false;
    }
  }

  public close(): void {
    this.router.navigate(['/home']);
  }
}