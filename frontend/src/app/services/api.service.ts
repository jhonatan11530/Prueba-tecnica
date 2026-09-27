import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) { }

  getPaises(): Observable<any> {
    return this.http.get(`${this.apiUrl}/paises`);
  }

  getCiudades(paisId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/paises/${paisId}/ciudades`);
  }

  consultar(ciudadId: number, presupuesto: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/consultas`, { ciudad_id: ciudadId, presupuesto });
  }

  getHistorial(): Observable<any> {
    return this.http.get(`${this.apiUrl}/consultas/historial`);
  }
}