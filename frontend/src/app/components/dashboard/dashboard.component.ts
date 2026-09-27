import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit {
  paises: any[] = [];
  ciudades: any[] = [];
  
  selectedPais: number | null = null;
  selectedCiudad: number | null = null;
  presupuesto: number | null = null;

  loadingPaises = false;
  loadingCiudades = false;
  loadingConsulta = false;

  resultado: any = null;
  errorMsg = '';
  validationErrors: any[] = [];

  constructor(
    private apiService: ApiService, 
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.cargarPaises();
  }

  cargarPaises() {
    this.loadingPaises = true;
    this.apiService.getPaises().subscribe({
      next: (res) => {
        this.paises = res.data;
        this.loadingPaises = false;
      },
      error: () => this.loadingPaises = false
    });
  }

  onPaisChange() {
    this.selectedCiudad = null;
    this.ciudades = [];
    this.resultado = null;
    
    if (this.selectedPais) {
      this.loadingCiudades = true;
      this.apiService.getCiudades(this.selectedPais).subscribe({
        next: (res) => {
          this.ciudades = res.data;
          this.loadingCiudades = false;
        },
        error: () => this.loadingCiudades = false
      });
    }
  }

  onSubmit() {
    if (!this.selectedCiudad || !this.presupuesto) return;

    this.loadingConsulta = true;
    this.errorMsg = '';
    this.resultado = null;
    this.validationErrors = [];

    this.apiService.consultar(this.selectedCiudad, this.presupuesto).subscribe({
      next: (res) => {
        this.resultado = res.data;
        this.loadingConsulta = false;
      },
      error: (err) => {
        this.loadingConsulta = false;
        if (err.status === 422) {
          this.errorMsg = err.error?.error?.message;
          this.validationErrors = err.error?.error?.details || [];
        } else if (err.status === 502) {
          this.errorMsg = 'Error 502: ' + (err.error?.error?.message || 'Servicios externos no disponibles.');
        } else {
          this.errorMsg = 'Ha ocurrido un error inesperado al procesar tu consulta.';
        }
      }
    });
  }

  getFieldError(field: string): string | null {
    const err = this.validationErrors.find(e => e.field === field);
    return err ? err.message : null;
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        this.authService.clearTokens();
        this.router.navigate(['/login']);
      },
      error: () => {
        this.authService.clearTokens();
        this.router.navigate(['/login']);
      }
    });
  }
}