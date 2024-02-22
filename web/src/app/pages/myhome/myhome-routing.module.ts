import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MyHomeComponent } from './my-home/my-home.component';
import { RouterModule } from '@angular/router';

const routes = [
  {
    path: 'services',
    component: MyHomeComponent
  }
];

@NgModule({
  declarations: [],
  imports: [
    RouterModule.forChild(routes)
  ],
  exports: [RouterModule]
})
export class MyhomeRoutingModule { }
