import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MyHomeComponent } from './my-home/my-home.component';
import { MyhomeRoutingModule } from './myhome-routing.module';



@NgModule({
  declarations: [MyHomeComponent],
  imports: [
    CommonModule,
    MyhomeRoutingModule
  ]
})
export class MyhomeModule { }
