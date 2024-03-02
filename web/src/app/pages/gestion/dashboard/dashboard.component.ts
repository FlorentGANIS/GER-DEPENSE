import { Component, OnInit } from '@angular/core';
import { OwlOptions } from 'ngx-owl-carousel-o';
import { ChartType } from 'src/app/shared/apex.model';

import { walletOverview, investedOverview, marketOverview, walletlineChart, tradeslineChart, investedlineChart, profitlineChart, recentActivity, News, transactionsAll, transactionsBuy, transactionsSell } from './data';
import { BudgetService } from 'src/app/shared/services/budget.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
/**
 *  Dashboard Component
 */
export class DashboardComponent implements OnInit {

  // bread crumb items
  breadCrumbItems!: Array<{}>;
  title!: string;
  dataSource!: Object;
  walletOverview!: ChartType;
  investedOverview!: ChartType;
  marketOverview!: ChartType;
  walletlineChart!: ChartType;
  tradeslineChart!: ChartType;
  investedlineChart!: ChartType; prevision_expense!: ChartType;
  profitlineChart!: ChartType;
  recentActivity: any;
  News: any;
  transactionsAll: any;
  transactionsBuy: any;
  transactionsSell: any;
  num: number = 0;

  budgets: any; incomes: any; fixed_charges: any; variables_charges: any; year: number = new Date().getFullYear();

  budget: any; categories: any[] = []; is_loading: boolean = false; sum_total_used: number = 0; solde: number = 0;
  // Coin News Slider
  timelineCarousel: OwlOptions = {
    items: 1,
    loop: false,
    margin: 0,
    nav: false,
    navText: ["", ""],
    dots: true,
    responsive: {
      680: {
        items: 4
      },
    }
  }
  year_recap: any;
  incomes_data: any[] = [];
  expenses_data: any[] = [];

  constructor(private budget_service: BudgetService) {
    this.initChart();
  }


  option = {
    startVal: this.num,
    useEasing: true,
    duration: 2,
    decimalPlaces: 2,
  };


  ngOnInit(): void {
    /**
     * BreadCrumb 
     */
    this.breadCrumbItems = [
      { label: 'Tableau de bord' },
      { label: 'Tableau de bord', active: true }
    ];

    /**
     * Fetches the data
     */
    this.countEntitiesApp();
    this.getRecapForChart();
  }

  initChart(listPrevisions: any = [], listExpenses: any = []) {
    this.prevision_expense = {
      chart: { height: 350, type: "bar", toolbar: { show: !1 } },
      plotOptions: { bar: { horizontal: !1, columnWidth: "45%" } },
      dataLabels: { enabled: !1 },
      stroke: { show: !0, width: 2, colors: ["transparent"] },
      series: [
        { name: "Prévisions", data: listPrevisions },
        { name: "Dépenses", data: listExpenses },
      ],
      colors: ['#2ab57d', '#fd625e'],
      xaxis: {
        categories: [
          'Jan', 'Fev', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Dec'
        ],
      },
      yaxis: { title: { text: "Montants (F CFA)", style: { fontWeight: "500" } } },
      grid: { borderColor: "#f1f1f1" },
      fill: { opacity: 1 },
      tooltip: {
        y: {
          formatter: (val: string) => {
            return val + ' F CFA';
          }
        }
      }
    };

  }

  getRecapForChart() {

    this.budget_service.racapChartDashboard({ year: null }).subscribe(
      {
        next: (v: any) => {
          if (v.status == 200) {
            this.year_recap = v.data;
            this.year_recap.forEach((element: any) => {
              this.incomes_data.push(element?.total_incomes)
              this.expenses_data.push(element?.total_expenses)
            });
            console.log(this.incomes_data)
            // console.log(this.expenses_data)
            this.initChart(this.incomes_data, this.expenses_data);
            
          }

        },
        error: (e) => {
          console.error(e);
        },
      }
    );

  }


  countEntitiesApp() {
    // this.is_loading = true;
    this.budget_service.allInfosApp().subscribe(
      {
        next: (v: any) => {
          if (v.status == 200) {
            this.budgets = v.data.budgets;
            this.incomes = v.data.incomes;
            this.fixed_charges = v.data.fixed_charges;
            this.variables_charges = v.data.variable_charges;
            //this.budget = v.data.current_month_data;
            console.log(this.budgets)
          }
        },
        error: (e) => {
          console.error(e);
        },
      }
    );
  }


}