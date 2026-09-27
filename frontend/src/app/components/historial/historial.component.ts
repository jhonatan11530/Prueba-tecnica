import { Component, OnInit } from '@angular/core';
import { ApiService } from '../../services/api.service';

@Component({
  selector: 'app-historial',
  templateUrl: './historial.component.html'
})
export class HistorialComponent implements OnInit {
  historial: any[] = [];
  loading = true;

  constructor(private apiService: ApiService) {}

  ngOnInit(): void {
    this.apiService.getHistorial().subscribe({
      next: (res) => {
        this.historial = res.data;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }
}