import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { IncomeService } from 'src/app/shared/services/income.service';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-detail-budget',
  standalone: false,
  templateUrl: './detail-budget.component.html',
  styleUrl: './detail-budget.component.scss'
})
export class DetailBudgetComponent implements OnInit {
  // bread crumb items
  breadCrumbItems!: Array<{}>;
  public Collapsed = false;
  public firstCollapse = false;
  public secondCollapse = false;
  public bothColleaps = false;

 id!: any; budget: any; is_loading: boolean = false; incomes: any[] = []; expense_form!: FormGroup; expenses: any[] = []; income_form!: FormGroup;

  repartitions: any; reps: any; is_processing: boolean = false; 

  message: string = ''; today!: Date; management_units: any; with_detail: boolean = false; total_amount_used_for_budget: number = 0; invoice: any; income_dialog: boolean = false;

  domain_url: string = environment.domainUrl; categories: any; barData: any; barOptions: any; total_fixed_charges: number = 0; total_variable_charges: number = 0; total_sum_incomes: number = 0;

  total_saving_charges: number = 0; budget_incomes: any; file_url: any; detail_dialog: boolean = false; expense_det: any; total_fixed_sum_used_by_rep: number = 0; total_fixed_balance_by_rep: number = 0;

  total_variable_sum_used_by_rep: number = 0; total_variable_balance_by_rep: number = 0; total_saving_sum_used_by_rep: number = 0; total_saving_balance_by_rep: number = 0; 

  constructor(private router: Router, private route: ActivatedRoute, private fb: FormBuilder, private income_service: IncomeService,
    private budget_service: BudgetService
  ) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Détails d\'un budget', active: true }
    ];

    this.id = this.route.snapshot.params['id'];
    this.budgetDetail();
    this.today = new Date();

  }

  budgetDetail() {
    this.is_loading = true;
    this.budget_service.show({ 'id': this.id }).subscribe(
      {
        next: (v: any) => {
          this.budget = v.data.budget;
          this.categories = v.data.categories;
          this.total_amount_used_for_budget = v.data.total_amount_used;
          this.incomes = v.data.incomes;
          this.repartitions = v.data.others_data;
          this.total_sum_incomes = v.data.total_sum_incomes_recup;
          this.total_fixed_charges = v.data.total_sum_cf_recup;
          this.total_variable_charges = v.data.total_sum_cv_recup;
          this.total_saving_charges = v.data.total_sum_saving_recup;
          this.total_fixed_sum_used_by_rep = v.data.total_fixed_sum_used_by_rep;
          this.total_variable_sum_used_by_rep = v.data.total_variable_sum_used_by_rep;
          this.total_saving_sum_used_by_rep = v.data.total_saving_sum_used_by_rep;
          this.total_fixed_balance_by_rep = v.data.total_fixed_balance_by_rep;
          this.total_variable_balance_by_rep = v.data.total_variable_balance_by_rep;
          this.total_saving_balance_by_rep = v.data.total_saving_balance_by_rep;
        
          this.is_loading = false;
        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }

      });
  }
}
