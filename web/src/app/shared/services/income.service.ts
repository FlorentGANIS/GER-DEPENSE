import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class IncomeService {

  constructor(private http: HttpClient) { }

  list() {
  	return this.http.get(environment.backend_url + '/income/list');
  }
  
  allIncomes(){
  	return this.http.get(environment.backend_url + '/income/all-incomes');
  }

  create(body:any) {
    return this.http.post(environment.backend_url + '/income/create', body);
  }

  delete(body:any) {
    return this.http.post(environment.backend_url + '/income/delete', body);
  }

  changeIncomeStatus(body:any) {
    return this.http.post(environment.backend_url + '/income/change-status', body);
  }
}
