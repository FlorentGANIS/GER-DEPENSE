import { Component } from '@angular/core';
import { BudgetService } from 'src/app/shared/services/budget.service';

@Component({
  selector: 'app-budget',
  standalone: false,
  templateUrl: './budget.component.html',
  styleUrl: './budget.component.scss'
})
export class BudgetComponent {
  // bread crumb items
  breadCrumbItems!: Array<{}>; is_processing: boolean = false; index_page: number = 0; true_current_month: string = '';
  budgets: any[] = [];
  totalRecords = 0;

  constructor(private budget_service: BudgetService) { }

  ngOnInit(): void {
    this.totalRecords = 10;
    /**
     * BreadCrumb Set
     */
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Budgets', active: true }
    ];

    this.listBudgets();


  }


  listBudgets() {
    this.is_processing = true;
    this.budget_service.list({}).subscribe(
      {
        next: (v: any) => {
          this.budgets = v.data;
          this.index_page = v.index_page;
          this.true_current_month = v.true_current_month.id;
          this.is_processing = false;
        },

        error: (e) => {
          console.error(e);
          this.is_processing = false;
        },

        complete: () => {
        }
      }
    );
  }




}
