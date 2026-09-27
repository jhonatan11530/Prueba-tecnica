import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html'
})
export class LoginComponent {
  credentials = {
    correo: '',
    password: ''
  };
  
  errorMsg = '';
  loading = false;

  constructor(private authService: AuthService, private router: Router) {
    if (this.authService.getToken()) {
      this.router.navigate(['/dashboard']);
    }
  }

  onSubmit() {
    this.loading = true;
    this.errorMsg = '';
    
    this.authService.login(this.credentials).subscribe({
      next: (res) => {
        this.router.navigate(['/dashboard']);
      },
      error: (err) => {
        this.loading = false;
        if (err.status === 429) {
          this.errorMsg = 'Has superado el límite de intentos. Intenta en un minuto.';
        } else {
          this.errorMsg = err.error?.error?.message || 'Error al iniciar sesión.';
        }
      }
    });
  }
}