import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { ChartType } from 'src/app/shared/apex.model';
import { BudgetService } from 'src/app/shared/services/budget.service';

@Component({
  selector: 'app-statistics',
  standalone: false,
  templateUrl: './statistics.component.html',
  styleUrl: './statistics.component.scss'
})
export class StatisticsComponent {
  breadCrumbItems!: Array<{}>; expenses: any[] = []; msg: string = '';  today!: Date; is_processing: boolean = false;

  stat_form!: FormGroup; p: number = 1; expensesChart!: ChartType;
  budgets: any[] = [];
  years: any;
  is_loading: boolean = false;
  stats: any[] = [];
  message: any;

  constructor(private fb: FormBuilder, private toastr: ToastrService, private budget_service: BudgetService) {
    this.pieChartStructure();
   }

  ngOnInit(): void {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Statistiques des dépenses', active: true }
    ];

    this.stat_form = this.fb.group({
      month_id: ['', [Validators.required]],

      year_budget: ['', [Validators.required]],
    });
    this.listDistinctYears();
    this.listBudgets();
    

  }

  listBudgets() {
    this.budget_service.list({ year: null }).subscribe(
      {
        next: (v: any) => {
          this.budgets = v.data;
        },
      }
    );
  }

  listDistinctYears(): void {
    this.budget_service.listYears().subscribe(
      {
        next: (v: any) => {
          this.years = v.data;
        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }
      }
    );
  }


  pieChartStructure(labels: any = [], data: any = []){
    this.expensesChart = {
      chart: { height: 320, type: "pie" },
      series: data,
      labels: labels,
      colors: ["#2ab57d", "#5156be", "#fd625e", "#4ba6ef", "#ffbf53"],
      legend: {
          show: !0,
          position: "bottom",
          horizontalAlign: "center",
          verticalAlign: "middle",
          floating: !1,
          fontSize: "14px",
          offsetX: 0,
      },
      responsive: [
          {
              breakpoint: 600,
              options: { chart: { height: 240 }, legend: { show: !1 } },
          },
      ],
  };
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }

  getStatistics() {
    this.is_loading = true;
    this.stats = [];
    this.budget_service.getBudgetStatistics(this.stat_form.value).subscribe({
      next: (v: any) => {
        this.message = v.message;
        if (v.status == 200) {
          this.pieChartStructure(v.data?.labels, v.data?.amounts)
          this.is_loading = false;
        } else {
          
          this.showError(this.message);
          this.is_loading = false;
        }
      },
      error: (e) => {
        console.error(e);
        
        this.is_loading = false;
      },
    });
  }

}
