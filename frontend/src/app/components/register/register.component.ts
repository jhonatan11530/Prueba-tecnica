import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html'
})
export class RegisterComponent {
  user = {
    nombre: '',
    correo: '',
    password: '',
    password_confirmation: ''
  };

  errorMsg = '';
  validationErrors: any[] = [];
  loading = false;
  successMsg = '';

  constructor(private authService: AuthService, private router: Router) {
    if (this.authService.getToken()) {
      this.router.navigate(['/dashboard']);
    }
  }

  onSubmit() {
    this.loading = true;
    this.errorMsg = '';
    this.validationErrors = [];
    
    this.authService.register(this.user).subscribe({
      next: (res) => {
        this.loading = false;
        this.successMsg = '¡Usuario registrado! Redirigiendo al login...';
        setTimeout(() => this.router.navigate(['/login']), 2000);
      },
      error: (err) => {
        this.loading = false;
        if (err.status === 422 && err.error?.error?.details) {
          this.errorMsg = err.error.error.message;
          this.validationErrors = err.error.error.details;
        } else if (err.status === 409) {
          this.errorMsg = 'El correo ya está registrado.';
        } else {
          this.errorMsg = 'Error inesperado al registrar el usuario.';
        }
      }
    });
  }

  getFieldError(field: string): string | null {
    const err = this.validationErrors.find(e => e.field === field);
    return err ? err.message : null;
  }
}