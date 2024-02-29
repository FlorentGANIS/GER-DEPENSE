import { CurrencyPipe } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { BudgetService } from 'src/app/shared/services/budget.service';
import { CategoryService } from 'src/app/shared/services/category.service';

@Component({
  selector: 'app-distribution',
  standalone: false,
  templateUrl: './distribution.component.html',
  styleUrl: './distribution.component.scss'
})
export class DistributionComponent implements OnInit {

  breadCrumbItems!: Array<{}>; display_form: boolean = false; distribution_form!: FormGroup; message = ""; display_modal: boolean = false; price_update_form!: FormGroup;

  loading: boolean = false; fixed_categories: any[] = []; form_title: string = "Répartition du budget"; category_form!: FormGroup; budget_id!: string; budget!: any; total_charges: number = 0; categories: any[] = [];

  label_button: string = "Répartir"; amount_to_distribute: number = 0; is_processing_to_share: boolean = false; category_dialog: boolean = false; items: any[] = [];

  global_amount: number = 0; is_loading: boolean = true; modal_title: string = ''; modal_text: string = ''; curr_categ_designation: string = ''; curre_categ_type: string = '';

  update_price_dialog: boolean = false; rowGroupMetadata: any; temp_amount_to_distribute: number = 0; is_processing_to_add: boolean = false; categories_already_seleted: any[] = []; reps: any[] = [];

  global_reps_amount: number = 0;

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
          rep_type: element.rep_type
        };

        // Montant des catégories à ajouter à part les charges fixes
        this.temp_amount_to_distribute += this.category_form.get('rep_amount')?.value;
        // Ajout dans le tableau à afficher 
        this.items.push(categ);
        // Init form
        this.category_form.reset();
        // Mise à jour du montant à distribuer
        this.distribution_form.patchValue({
          'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
        });
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
        this.distribution_form.patchValue({
          'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
        });
      }


    });

  }

  updateAmount() {

    if (!this.budget?.is_shared || this.budget?.is_shared == false) {
      this.items.forEach((element, idx) => {
        if (element.category_id == this.price_update_form.get('category_id')?.value) {
          element.rep_amount = this.price_update_form.get('rep_amount')?.value;
          // Retrait de la somme de la catégorie dans le montant à distribuer
          this.temp_amount_to_distribute -= this.price_update_form.get('old_rep_amount')?.value;
          // Mise à jour de la somme des montants à répartir
          this.temp_amount_to_distribute += this.price_update_form.get('rep_amount')?.value;
          // Mise à jour de la somme à afficher
          this.distribution_form.patchValue({
            'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute - this.temp_amount_to_distribute, 'XOF'),
          });
        }
      });
    } else {
      this.items.forEach((element, idx) => {
        if (element.category_id == this.price_update_form.get('category_id')?.value) {
          element.rep_amount = this.price_update_form.get('rep_amount')?.value;
          // Retrait de la somme de la catégorie dans le montant à distribuer
          this.global_reps_amount -= this.price_update_form.get('old_rep_amount')?.value;
          // Mise à jour de la somme des montants à répartir
          this.global_reps_amount += this.price_update_form.get('rep_amount')?.value;
          // Mise à jour de la somme à afficher
          this.amount_to_distribute = this.budget?.global_amount - this.global_reps_amount;
          this.distribution_form.patchValue({
            'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute, 'XOF'),
          });
        }
      });
    }

    this.update_price_dialog = false;
  }

  displayModalUpdPrice(stat: boolean, repartition: any) {
    if (stat) {
      this.price_update_form.patchValue({
        category_id: [repartition.category_id],
        designation: [repartition.rep_name],
        old_rep_amount: [repartition.rep_amount],
        rep_amount: [repartition.rep_amount]
      });

      this.update_price_dialog = true;
    } else {
      this.update_price_dialog = false;
      this.price_update_form.reset();
    }
  }

  displayModal(content: any, status: boolean) {
    if (status) {
      this.modalService.open(content, { centered: true });
    } else {
      this.modalService.dismissAll();
      this.category_form.reset();
    }
    
  }

  openForm() {
    this.display_form = true;
  }

  closeForm() {
    this.router.navigate(['/gestion/budgets']);
  }

  budgetDetail() {
    this.is_loading = true;
    this.budget_service.detailBeforeShareOut({ 'id': this.budget_id }).subscribe(
      {
        next: (v: any) => {
          this.budget = v.data.budget;
          this.global_amount = this.budget.global_amount;
          this.fixed_categories = v.data.fixed_categories;

          // Si le budget a été déjà réparti une fois
          if (this.budget.is_shared || this.budget.is_shared == true) {

            // Total des montants des répartitions
            this.global_reps_amount = v.data.repartitions_amount;
            // Montant restant à répartir
            this.amount_to_distribute = this.budget?.remaining_amount;
            // Les répartitions
            this.reps = v.data.repartitions;
            if (this.reps) {
              this.reps.forEach(rep => {
                const category = {
                  category_id: rep.category_id,
                  rep_amount: rep.rep_amount,
                  rep_name: rep?.category?.designation,
                  rep_type: rep?.category?.type
                };
                this.items.push(category);
                this.categories.forEach((categ, index) => {
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
              this.fixed_categories.forEach(fcateg => {
                this.curr_categ_designation = fcateg.designation;
                this.curre_categ_type = "FIXE";

                const category = {
                  category_id: fcateg.id,
                  rep_amount: fcateg.fixed_amount,
                  rep_name: this.curr_categ_designation,
                  rep_type: this.curre_categ_type
                };
                this.items.push(category);

              });
            }

          }

          this.distribution_form.patchValue({
            'global_amount': this.currencyPipe.transform(this.global_amount, 'XOF'),
            'distribution_amount': this.currencyPipe.transform(this.amount_to_distribute, 'XOF'),
            'budget_id': this.budget?.id
          });
          this.is_loading = false;


        },

        error: (e) => {
          console.error(e);
        },

        complete: () => {
        }

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
  }

  showSuccess(msg: string) {
    this.toastr.success("Succès", msg)
  }

  showError(msg: string) {
    this.toastr.error("Echec", msg)
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


}
