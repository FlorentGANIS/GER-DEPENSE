import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { TokenService } from './token.service';

@Injectable({
  providedIn: 'root'
})
export class AuthStateService {

  private loggedIn = new BehaviorSubject<boolean>(this.token.loggedIn());
  authStatus = this.loggedIn.asObservable();

  changeAuthStatus(value:boolean){
    //console.log(value)
    this.loggedIn.next(value);
    //console.log(this.token.loggedIn())
  }

  constructor(private token: TokenService) { }

}
