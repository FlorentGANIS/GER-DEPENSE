import { ComponentFixture, TestBed } from '@angular/core/testing';

import { VariableChargeComponent } from './variable-charge.component';

describe('VariableChargeComponent', () => {
  let component: VariableChargeComponent;
  let fixture: ComponentFixture<VariableChargeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VariableChargeComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(VariableChargeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
