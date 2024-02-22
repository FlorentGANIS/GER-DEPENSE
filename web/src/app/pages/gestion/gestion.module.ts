import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GestionRoutingModule } from './gestion-routing.module';
import { ReactiveFormsModule } from '@angular/forms';
import { WidgetModule } from 'src/app/shared/widget/widget.module';
import { CountUpModule } from 'ngx-countup';
import { NgApexchartsModule } from 'ng-apexcharts';
import { SimplebarAngularModule } from 'simplebar-angular';
import { NgbAlertModule, NgbDropdownModule, NgbNavModule, NgbPaginationModule, NgbToastModule } from '@ng-bootstrap/ng-bootstrap';
import { TablesModule } from '../tables/tables.module';
import { ChartModule } from '../chart/chart.module';
import { NgxUiLoaderModule } from 'ngx-ui-loader';
import { SharedModule } from 'src/app/shared/shared.module';
import { DashboardComponent } from './dashboard/dashboard.component';
import { BudgetComponent } from './budget/budget.component';
import { DetailBudgetComponent } from './detail-budget/detail-budget.component';
import { DistributionComponent } from './distribution/distribution.component';
import { ExpenseComponent } from './expense/expense.component';
import { FixedChargeComponent } from './fixed-charge/fixed-charge.component';
import { IncomeComponent } from './income/income.component';
import { RecapComponent } from './recap/recap.component';
import { StatisticsComponent } from './statistics/statistics.component';
import { StatisticByExpenseComponent } from './statistic-by-expense/statistic-by-expense.component';
import { VariableChargeComponent } from './variable-charge/variable-charge.component';
import { NgxPaginationModule } from 'ngx-pagination';
import { SavingComponent } from './saving/saving.component';



@NgModule({
  declarations: [DashboardComponent, BudgetComponent, DetailBudgetComponent,
  DistributionComponent, ExpenseComponent, FixedChargeComponent, IncomeComponent, SavingComponent,
RecapComponent, StatisticsComponent, StatisticByExpenseComponent, VariableChargeComponent],
  imports: [
    CommonModule,
    WidgetModule,
    CountUpModule,
    SharedModule,
    NgApexchartsModule,
    SimplebarAngularModule,
    NgbDropdownModule,
    NgbNavModule,
    ReactiveFormsModule,
    TablesModule,
    ChartModule,
    NgbPaginationModule,
    NgbToastModule,
    NgbAlertModule,
    NgxUiLoaderModule,
    GestionRoutingModule,
    SharedModule,
    ReactiveFormsModule,
    NgbDropdownModule,
    NgbPaginationModule,
    NgbToastModule,
    NgbAlertModule,
    NgxPaginationModule,
    NgxUiLoaderModule
  ]
})
export class GestionModule { }
