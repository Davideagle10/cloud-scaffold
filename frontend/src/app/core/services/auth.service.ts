import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = `${environment.API_BASE_URL}/auth`;
  private readonly TOKEN_KEY = 'authToken';

  constructor(private http: HttpClient, private router: Router) { }

  /**
   * Sends login credentials to the backend API and stores the token on success.
   * @param credentials An object containing 'usuario' and 'password'.
   * @returns An Observable with the API response.
   */
  login(credentials: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/login`, credentials).pipe(
      tap(response => {
        if (response && response.token) {
          localStorage.setItem(this.TOKEN_KEY, response.token);
        }
      })
    );
  }

  /**
   * Removes the token from storage and navigates to the login page.
   */
  logout(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    this.router.navigate(['/auth/login']);
  }

  /**
   * Retrieves the stored authentication token.
   * @returns The token string or null if not found.
   */
  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  /**
   * Checks if a user is authenticated by verifying the presence of a token.
   * @returns True if a token exists, false otherwise.
   */
  isAuthenticated(): boolean {
    return !!this.getToken();
  }
}