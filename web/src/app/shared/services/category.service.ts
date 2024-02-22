import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class CategoryService {

  constructor(private http: HttpClient) { }

  allCategories() {
  	return this.http.get(environment.backend_url + '/category/all');
  }

  categoriesWithoutFixed() {
  	return this.http.get(environment.backend_url + '/category/without-fixed');
  }


  fixedChargeList() {
  	return this.http.get(environment.backend_url + '/category/fixed/list');
  }

  variableChargeList() {
  	return this.http.get(environment.backend_url + '/category/variable/list');
  }

  savingChargeList() {
  	return this.http.get(environment.backend_url + '/category/saving/list');
  }

  create(body:any) {
    return this.http.post(environment.backend_url + '/category/create', body);
  }

  delete(body:any) {
    return this.http.post(environment.backend_url + '/category/delete', body);
  }

  changeCategoryStatus(body:any) {
    return this.http.post(environment.backend_url + '/category/change-status', body);
  }

}
