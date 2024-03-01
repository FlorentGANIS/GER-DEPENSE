import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BudgetService {

  constructor(private http: HttpClient) { }

  allInfosApp() {
  	return this.http.get(environment.backend_url + '/budget/all-infos-app');
  }

  racapChartDashboard(body: any) {
  	return this.http.post(environment.backend_url + '/budget/dashboard-recap', body);
  }

  listYears() {
  	return this.http.get(environment.backend_url + '/budget/list/years');
  }

  otherYears() {
  	return this.http.get(environment.backend_url + '/budget/other/years');
  }

  list(body: any) {
  	return this.http.post(environment.backend_url + '/budget/list', body);
  }

  getBudgetDetail() {
  	return this.http.get(environment.backend_url + '/budget/actif-budget-detail');
  }

  getBudgetStatistics(body:any) {
  	return this.http.post(environment.backend_url + '/budget/statistic', body);
  }

  getBudgetStatisticsByExpense(body:any) {
  	return this.http.post(environment.backend_url + '/budget/statistic-by-expense', body);
  }

  distinctChargesList() {
  	return this.http.get(environment.backend_url + '/budget/distinct-charge');
  }

  create(body:any) {
    return this.http.post(environment.backend_url + '/budget/create', body);
  }

  show(body:any) {
    return this.http.post(environment.backend_url + '/budget/show', body);
  }

  edit(body:any) {
    return this.http.post(environment.backend_url + '/budget/edit', body);
  }

  detailBeforeShareOut(body:any) {
    return this.http.post(environment.backend_url + '/budget/detail-before-share', body);
  }

  shareOut(body:any) {
    return this.http.post(environment.backend_url + '/budget/share-out', body);
  }

  delete(body:any) {
    return this.http.post(environment.backend_url + '/budget/delete', body);
  }

  recapBudget(body:any) {
    return this.http.post(environment.backend_url + '/budget/recap', body);
  }

  addIncome(body:any) {
    return this.http.post(environment.backend_url + '/budget/add-income', body);
  }

  deleteIncomeBudget(body:any) {
    return this.http.post(environment.backend_url + '/budget/delete-income-budget', body);
  }

  closeBudget(body:any) {
    return this.http.post(environment.backend_url + '/budget/close', body);
  }
}
