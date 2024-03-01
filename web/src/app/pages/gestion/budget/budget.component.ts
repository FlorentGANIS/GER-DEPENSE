import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { IncomeService } from 'src/app/shared/services/income.service';

@Component({
  selector: 'app-budget',
  standalone: false,
  templateUrl: './budget.component.html',
  styleUrl: './budget.component.scss'
})
export class BudgetComponent implements OnInit{
  // bread crumb items
  breadCrumbItems!: Array<{}>; is_processing: boolean = false; index_page: number = 0; true_current_month: string = '';
  budgets: any[] = []; budget_form!: FormGroup; incomes: any[] = [];
  year: number = new Date().getFullYear();
  curr_name: any;
  curr_month: any;
  message: any;
  display_form: boolean = false;

  constructor(private fb: FormBuilder, private budget_service: BudgetService, private income_service: IncomeService,
    private toastr: ToastrService, private router: Router,private ngxLoader: NgxUiLoaderService) { }

  ngOnInit(): void {
   
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Budgets', active: true }
    ];

    this.listBudgets();
    this.listIncomes();

    this.budget_form = this.fb.group({
      id: [''],
      incomes: this.fb.array([this.createIncomesGroup()])
    });

  }

  createIncomesGroup() {
    return this.fb.group({
      income_id: ['', [Validators.required]],
      income_amount: [null, [Validators.required]],
    });
  }

  public get incomesList(): FormArray {
    return <FormArray>this.budget_form.get('incomes');
  }

  public addIncome() {
    this.incomesList.push(this.createIncomesGroup());
  }

  public deleteIncome(index: number): void {
    this.incomesList.removeAt(index);
    this.incomesList.markAsDirty();
  }

  listIncomes() {
    this.income_service.list().subscribe({
      next: (v: any) => {
        this.incomes = v.data;
      }
    });
  }


  listBudgets() {
    this.ngxLoader.startLoader('loader-spin');
    this.budget_service.list({}).subscribe(
      {
        next: (v: any) => {
          this.budgets = v.data;
          this.index_page = v.index_page;
          this.true_current_month = v.true_current_month.id;
         this.ngxLoader.stopLoader('loader-spin');
        },

        error: (e) => {
          console.error(e);
         this.ngxLoader.stopLoader('loader-spin');
        },

        complete: () => {
        }
      }
    );
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }

  createOrManageBudget(budget: any) {
    if (budget?.status) {
      this.router.navigate(['gestion/detail-budget', budget.budget_id]);
    } else {
      this.curr_name = budget?.budget_name;
      this.curr_month = budget?.month_id;
      this.display_form = true;
    }
  }



  saveBudget() {    
    this.is_processing = true;
    this.budget_service.create({ form: this.budget_form.value, month: this.curr_month }).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.budget_form.reset();
            this.showSuccess(this.message);
            this.listBudgets();
            this.display_form = false;
            this.is_processing = false;
          } else {
            this.showError(this.message);
            this.is_processing = false;
          }
        },

        error: (e) => {
          console.error(e);
          this.message = 'Une erreur interne est survenue. Veuillez contacter l\'administrateur.';
          this.showError(this.message);
          this.is_processing = false;
        },

        complete: () => {

        }
      }
    )

  }

  closeForm() {
    this.display_form = false;
    this.budget_form.reset();
  }

}
