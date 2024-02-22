import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class ExpenseService {

  constructor(private http: HttpClient) { }

  list(body: any) {
  	return this.http.post(environment.backend_url + '/expense/list', body);
  }

  listUnitsManagement() {
  	return this.http.get(environment.backend_url + '/expense/unit/list');
  }

  create(body:any) {
    return this.http.post(environment.backend_url + '/expense/create', body);
  }

  delete(body:any) {
    return this.http.post(environment.backend_url + '/expense/delete', body);
  }

  detail(body:any) {
    return this.http.post(environment.backend_url + '/expense/detail', body);
  }
}
