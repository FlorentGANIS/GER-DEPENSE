import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { NgxUiLoaderService } from 'ngx-ui-loader';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { CategoryService } from 'src/app/shared/services/category.service';
import { ExpenseService } from 'src/app/shared/services/expense.service';

@Component({
  selector: 'app-expense',
  standalone: false,
  templateUrl: './expense.component.html',
  styleUrl: './expense.component.scss'
})
export class ExpenseComponent implements OnInit{

  breadCrumbItems!: Array<{}>; expenses: any[] = []; msg: string = '';  today!: Date; is_processing: boolean = false;

  search_form!: FormGroup; p: number = 1;

  constructor(private fb: FormBuilder, private expense_service: ExpenseService, private budget_service: BudgetService,
    private category_service: CategoryService, private ngxLoader: NgxUiLoaderService, private toastr: ToastrService,) { }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Toutes les dépenses', active: true }
    ];

    this.today = new Date();

    this.search_form = this.fb.group({
      exp_amount: [''],
      quantity: [''],
      expense_date: [''],
      category_id: [''],
      management_unit_id: [''],
      year_budget: [''],
      month_id: [''],
    });
    this.listExpenses();

  }

  listExpenses() {
    this.ngxLoader.startLoader('loader-spin');
    this.expense_service.list(this.search_form.value).subscribe(
      {
        next: (v: any) => {
          if(v.status == 200){
            this.expenses = v.data;
            this.ngxLoader.stopLoader('loader-spin');
          }          
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
}
