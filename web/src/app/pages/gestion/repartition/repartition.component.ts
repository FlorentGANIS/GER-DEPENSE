import { Component, OnInit } from '@angular/core';
import { CurrencyPipe } from '@angular/common';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { CategoryService } from 'src/app/shared/services/category.service';
import { CategoryCustom } from 'src/app/shared/models/category-custom';

@Component({
  selector: 'app-repartition',
  standalone: false,
  templateUrl: './repartition.component.html',
  styleUrl: './repartition.component.scss'
})
export class RepartitionComponent implements OnInit{

  
  breadCrumbItems!: Array<{}>; display_form: boolean = false; distribution_form!: FormGroup; message = ""; display_modal: boolean = false; price_update_form!: FormGroup;

  loading: boolean = false; incomes_budget: any; fixed_categories: any[] = []; form_title: string = "Répartition du budget"; category_form!: FormGroup; budget_id!: string; budget!: any; total_charges: number = 0; categories: any[] = [];

  label_button: string = "Répartir"; amount_to_distribute: number = 0; is_processing_to_share: boolean = false; category_dialog: boolean = false; items: any[] = []; distribution_amount: number = 0;

  global_amount: number = 0; is_loading: boolean = true; modal_title: string = ''; modal_text: string = ''; curr_categ_designation: string = ''; curre_categ_type: string = '';

  update_price_dialog: boolean = false; rowGroupMetadata: any; temp_amount_to_distribute: number = 0; is_processing_to_add: boolean = false; categories_already_seleted: any[] = []; reps: any[] = [];

  global_reps_amount: number = 0; content_detail_rep_form!: FormGroup; item_rep_details: any;
  category_info: any;

  constructor(private route: ActivatedRoute, private fb: FormBuilder, private budget_service: BudgetService,
    
    private category_service: CategoryService, private router: Router, private toastr: ToastrService, private modalService: NgbModal,

    private currencyPipe: CurrencyPipe) { }

  ngOnInit() {
    this.breadCrumbItems = [
      { label: 'Gestion' },
      { label: 'Répartition', active: true }
    ];
    this.budget_id = this.route.snapshot.params['id'];
    this.distribution_form = this.fb.group({
      id: [''],
      budget_id: [null],
      distribution_amount: [null],
      remaining_amount: [null],
      global_amount: [null],
    });

    this.category_form = this.fb.group({
      category_id: [null, [Validators.required]],
      rep_amount: [null, [Validators.required]],
    });    

    this.budgetDetail();

    this.category_form?.valueChanges.subscribe((v) => {
      this.verifyIfAmountIsUp(this.category_form?.get('rep_amount')?.value);
    });

    this.getCategoriesWithoutFixe();

    this.price_update_form = this.fb.group({
      rep_id: [null],
      category_id: [null, [Validators.required]],
      designation: [''],
      rep_amount: [null],
      old_rep_amount: [null]
    });

    this.content_detail_rep_form = this.fb.group({
      category_id: [null, [Validators.required]],
      income_budget_id: [null, [Validators.required]],
      rep_detail_amount: [null, [Validators.required]],
    });


  }

  getCategoriesWithoutFixe() {
    this.category_service.categoriesWithoutFixed().subscribe(
      {
        next: (v: any) => {
          if (v.status == 200) {
            this.categories = v.data;
          }
        },
        error: (e) => {
          console.error(e);
        },
        complete: () => {
        }
      }
    );
  }*

  addCategoryToRepDetails(){

  }

  budgetDetail() {
    this.is_loading = true;
    this.budget_service.detailBeforeShareOut({ 'id': this.budget_id }).subscribe(
      {
        next: (v: any) => {
          this.budget = v.data.budget;
          this.global_amount = this.budget.global_amount;
          this.fixed_categories = v.data.fixed_categories;
          this.incomes_budget = v.data.incomes_budget;
          console.log(this.incomes_budget)

          // Si le budget a été déjà réparti une fois
          if (this.budget.is_shared || this.budget.is_shared == true) {

            // Total des montants des répartitions
            this.global_reps_amount = v.data.repartitions_amount;
            // Montant restant à répartir
            this.distribution_amount = this.budget?.remaining_amount;
            this.amount_to_distribute = this.budget?.remaining_amount;
            // Les répartitions
            this.reps = v.data.repartitions;
            if (this.reps) {
              this.reps.forEach((rep: any) => {
                const category = {
                  category_id: rep.category_id,
                  rep_amount: rep.rep_amount,
                  rep_name: rep?.category?.designation,
                  rep_type: rep?.category?.type,
                  rep_details: []
                };
                this.items.push(category);
                this.categories.forEach((categ: any, index: any) => {
                  if (rep.category_id == categ.category_id) {
                    // Ajout dans le tableau des catégories déjà ajouter dans les items
                    this.categories_already_seleted.push(categ);
                    // Suppression de cette catégorie ajoutée dans la liste des catégories à sélectionner
                    this.categories.splice(index, 1);
                  }
                });
              });
            }
          }

          // Si le budget n'a jamais été réparti
          if (!this.budget.is_shared || this.budget.is_shared == false) {

            this.amount_to_distribute = this.global_amount - v.data.total;

            if (this.fixed_categories) {
              this.fixed_categories.forEach((fcateg: any) => {
                this.curr_categ_designation = fcateg.designation;
                this.curre_categ_type = "FIXE";

                const category = {
                  category_id: fcateg.id,
                  rep_amount: fcateg.fixed_amount,
                  rep_name: this.curr_categ_designation,
                  rep_type: this.curre_categ_type,
                  rep_details: []
                };
                this.items.push(category);

              });
            }

          }
         
          // this.distribution_form.patchValue({
          //   'global_amount': this.currencyPipe.transform(this.global_amount, 'XOF'),
          //   'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute, 'XOF'),
          //   'budget_id': this.budget?.id
          // });
          this.is_loading = false;


        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }

      });
  }

   // Vérifier si le montant inséré est supérieur au montant à répartir
   verifyIfAmountIsUp(amount: number) {
    if ((this.amount_to_distribute - this.temp_amount_to_distribute) < amount) {
      this.display_modal = true;
      this.is_processing_to_add = false;
      return;
    } else {
      this.is_processing_to_add = this.category_form.valid;
    }
  }

  addCategory() {
    // Trouver la catégorie correspondante 
    this.categories.forEach((element, index) => {
      if (element.category_id == this.category_form.get('category_id')?.value) {
        const categ = {
          category_id: this.category_form.get('category_id')?.value,
          rep_amount: this.category_form.get('rep_amount')?.value,
          rep_name: element.rep_name,
          rep_type: element.rep_type,
          rep_details: []
        };

        // Montant des catégories à ajouter à part les charges fixes
        this.temp_amount_to_distribute += this.category_form.get('rep_amount')?.value;
        // Ajout dans le tableau à afficher 
        this.items.push(categ);
        // Init form
        this.category_form.reset();
        // Mise à jour du montant à distribuer
        // this.distribution_form.patchValue({
        //   'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
        // });
        this.distribution_amount = this.amount_to_distribute - this.temp_amount_to_distribute;
        // Ajout dans le tableau des catégories déjà ajouter dans les items
        this.categories_already_seleted.push(element);
        // Suppression de cette catégorie ajoutée dans la liste des catégories à sélectionner
        this.categories.splice(index, 1);
      }
    });

  }

  removeCategory(category: any, index: number) {
    // Recherche de la catégorie à supprimer de la liste des items affichés dans la liste des catégories déjà ajoutées
    this.categories_already_seleted.forEach((elt, idx) => {
      // Si catégorie trouvée
      if (elt.category_id == category.category_id) {

        // Réinsertion de la catégorie dans la liste des catégories à sélectionner
        this.categories.push(elt);
        // Suppression de cette catégorie dans la liste des catégories déjà sélectionnées
        this.categories_already_seleted.splice(idx, 1);
        // Suppression de la catégorie dans la liste des items affichés
        this.items.splice(index, 1);
        // Retrait de la somme de la catégorie dans le montant à distribuer
        this.temp_amount_to_distribute -= category.rep_amount;
        // Mise à jour de la somme à afficher
        // this.distribution_form.patchValue({
        //   'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
        // });
        this.distribution_amount = this.amount_to_distribute - this.temp_amount_to_distribute;
      }


    });

  }

  updateAmount() {

    if (!this.budget?.is_shared || this.budget?.is_shared == false) {
      this.items.forEach((element: any, idx: any) => {
        if (element.category_id == this.price_update_form.get('category_id')?.value) {
          element.rep_amount = this.price_update_form.get('rep_amount')?.value;
          // Retrait de la somme de la catégorie dans le montant à distribuer
          this.temp_amount_to_distribute -= this.price_update_form.get('old_rep_amount')?.value;
          // Mise à jour de la somme des montants à répartir
          this.temp_amount_to_distribute += this.price_update_form.get('rep_amount')?.value;
          // Mise à jour de la somme à afficher
          // this.distribution_form.patchValue({
          //   'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
          // });
          this.distribution_amount = this.amount_to_distribute - this.temp_amount_to_distribute;
        }
      });
    } else {
      this.items.forEach((element: any, idx: any) => {
        if (element.category_id == this.price_update_form.get('category_id')?.value) {
          element.rep_amount = this.price_update_form.get('rep_amount')?.value;
          // Retrait de la somme de la catégorie dans le montant à distribuer
          this.global_reps_amount -= this.price_update_form.get('old_rep_amount')?.value;
          // Mise à jour de la somme des montants à répartir
          this.global_reps_amount += this.price_update_form.get('rep_amount')?.value;
          // Mise à jour de la somme à afficher
          this.amount_to_distribute = this.budget?.global_amount - this.global_reps_amount;
          // this.distribution_form.patchValue({
          //   'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute, 'XOF'),
          // });
          this.distribution_amount = this.amount_to_distribute;
        }
      });
    }

    this.modalService.dismissAll();
    this.price_update_form.reset();
  }

  displayModalUpdPrice(contentModifPrice: any, stat: boolean, repartition: any ) {
    if (stat) {
      this.price_update_form.patchValue({
        category_id: [repartition.category_id],
        designation: [repartition.rep_name],
        old_rep_amount: [repartition.rep_amount],
        rep_amount: [repartition.rep_amount]
      });

      this.modalService.open(contentModifPrice, { centered: true });
    } else {
      this.modalService.dismissAll();
      this.price_update_form.reset();
    }
  }

  addCategoryToDetailRep(){
    //console.log(this.content_detail_rep_form.value)
    let income_label = '';
    this.incomes_budget.forEach((inc: any) => {
      if(inc.income_id == this.content_detail_rep_form.get('income_budget_id')?.value){
        income_label = inc.income?.label;
      }
    });
    this.items.forEach((elt: any) => {
      if(elt.category_id == this.content_detail_rep_form.get('category_id')?.value) {        
        const rep_detail_format = {
          category_id: this.content_detail_rep_form.get('category_id')?.value,
          rep_detail_amount: this.content_detail_rep_form.get('rep_detail_amount')?.value,
          income_budget_id: this.content_detail_rep_form.get('income_budget_id')?.value,
          income_budget_label: income_label,
        };
        elt.rep_details.push(rep_detail_format);
        this.content_detail_rep_form.controls['income_budget_id'].reset();
        this.content_detail_rep_form.controls['rep_detail_amount'].reset();
      
      }
    });
    console.log(this.items)
  }

  displayDetailRepartition(contentDetailRepartition: any, status: boolean, category: any) {
    console.log(category)
    if (status) {
      this.category_info = category;
      this.content_detail_rep_form.patchValue({category_id: category?.category_id});
      this.item_rep_details = category.rep_details;
      this.modalService.open(contentDetailRepartition, { size: 'lg', windowClass: 'modal-holder', centered: false });
    } else {
      this.modalService.dismissAll();
      //this.category_form.reset();
    }
    
  }

  displayModal(content: any, status: boolean) {
    if (status) {
      this.modalService.open(content);
    } else {
      this.modalService.dismissAll();
      this.category_form.reset();
    }
    
  }

  shareOut() {
    this.is_processing_to_share = true;
    this.distribution_form.patchValue({ 'remaining_amount': this.amount_to_distribute - this.temp_amount_to_distribute });

    this.budget_service.shareOut({ distribution_form: this.distribution_form.value, items: this.items }).subscribe(
      {
        next: (v: any) => {
          this.message = v.message;
          if (v.status == 200) {
            this.showSuccess(this.message);
            this.is_processing_to_share = false;
            setTimeout(() => {
              this.router.navigate(['/gestion/budgets']);
              this.distribution_form.reset();
            }, 1000);

          } else {
            this.showError(this.message);
            this.is_processing_to_share = false;
          }
        },

        error: (e) => {
          console.error(e);
          this.message = 'Une erreur interne est survenue. Veuillez contacter l\'administrateur.';
          this.showError(this.message);
          this.is_processing_to_share = false;
        },

        complete: () => {

        }
      }
    )

  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
  }

  openForm() {
    this.display_form = true;
  }

  closeForm() {
    this.router.navigate(['/gestion/budgets']);
  }


}
