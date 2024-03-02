import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class EnvelopeService {

  constructor(private httpClient: HttpClient) { }

  list(){
    return this.httpClient.get(environment.backend_url + '/envelope/list');
  }
  
  historyEnvelopes(body: any){
    return this.httpClient.post(environment.backend_url + '/envelope/history', body);
  }
}
