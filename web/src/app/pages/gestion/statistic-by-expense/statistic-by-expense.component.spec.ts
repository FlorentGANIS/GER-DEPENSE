import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StatisticByExpenseComponent } from './statistic-by-expense.component';

describe('StatisticByExpenseComponent', () => {
  let component: StatisticByExpenseComponent;
  let fixture: ComponentFixture<StatisticByExpenseComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StatisticByExpenseComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(StatisticByExpenseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
