import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LayoutComponent } from './layouts/layout.component';

const routes: Routes = [
  { path: '', loadChildren: () => import('./account/account.module').then(m => m.AccountModule) },
  { path: 'gestion', component: LayoutComponent, loadChildren: () => import('./pages/gestion/gestion.module').then(m => m.GestionModule) },
  { path: 'pages', loadChildren: () => import('./extrapages/extrapages.module').then(m => m.ExtrapagesModule) },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { scrollPositionRestoration: 'top' })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
