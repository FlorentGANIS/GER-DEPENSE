import { HttpBackend, HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { User } from '../models/user';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private http: HttpClient;
  // Pour éviter le http interceptor
  constructor( handler: HttpBackend) { 
    this.http = new HttpClient(handler);
 }

  // User registration
  register(user: User){
    return this.http.post(environment.backend_url + '/auth/register', user);
  }

  // Login
  login(user: User){
    return this.http.post<any>(environment.backend_url + '/auth/login', user);
  }

  getConfirmationCode(body: any) {
    return this.http.post(environment.backend_url + '/code/confirmation', body);
  }

  getNewConfirmationCode(body: any) {
    return this.http.post(environment.backend_url + '/new/code/confirmation', body);
  }

  verificationCode(body: any) {
    return this.http.post(environment.backend_url + '/verify/code', body);
  }
}
