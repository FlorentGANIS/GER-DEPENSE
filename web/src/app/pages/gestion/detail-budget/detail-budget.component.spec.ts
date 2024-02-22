import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DetailBudgetComponent } from './detail-budget.component';

describe('DetailBudgetComponent', () => {
  let component: DetailBudgetComponent;
  let fixture: ComponentFixture<DetailBudgetComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DetailBudgetComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(DetailBudgetComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
