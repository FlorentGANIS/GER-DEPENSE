import { NgModule } from '@angular/core';
import { DashboardComponent } from './dashboard/dashboard.component';
import { RouterModule } from '@angular/router';
import { IncomeComponent } from './income/income.component';
import { FixedChargeComponent } from './fixed-charge/fixed-charge.component';
import { VariableChargeComponent } from './variable-charge/variable-charge.component';
import { SavingComponent } from './saving/saving.component';
import { BudgetComponent } from './budget/budget.component';
import { DetailBudgetComponent } from './detail-budget/detail-budget.component';
import { DistributionComponent } from './distribution/distribution.component';
import { ExpenseComponent } from './expense/expense.component';
import { StatisticsComponent } from './statistics/statistics.component';
import { StatisticByExpenseComponent } from './statistic-by-expense/statistic-by-expense.component';
import { RecapComponent } from './recap/recap.component';
import { EnvelopeComponent } from './envelope/envelope.component';
import { HistoryEnvelopeComponent } from './history-envelope/history-envelope.component';

const routes = [
  {
    path: 'dashboard',
    component: DashboardComponent
  },
  { path: 'sources-revenus', component: IncomeComponent },
  { path: 'charges-fixes', component: FixedChargeComponent },
  { path: 'charges-variables', component: VariableChargeComponent },
  { path: 'epargnes', component: SavingComponent },
  { path: 'budgets', component: BudgetComponent },
  { path: 'detail-budget/:id', component: DetailBudgetComponent },
  //{ path: 'maj-budget/:id', component: UpdateBudgetComponent },
  { path: 'distribution/budget/:id', component: DistributionComponent },
  { path: 'depenses', component: ExpenseComponent },
  { path: 'stat/statistique-par-previsions', component: StatisticsComponent },
  { path: 'stat/statistique-par-depenses', component: StatisticByExpenseComponent },
  { path: 'recap', component: RecapComponent },
  { path: 'enveloppe', component: EnvelopeComponent },
  { path: 'historique-enveloppe', component: HistoryEnvelopeComponent },

];

@NgModule({
  declarations: [],
  imports: [
    RouterModule.forChild(routes)
  ],
  exports: [RouterModule]
})
export class GestionRoutingModule { }
