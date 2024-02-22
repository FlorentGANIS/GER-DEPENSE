import { ComponentFixture, TestBed } from '@angular/core/testing';

import { FixedChargeComponent } from './fixed-charge.component';

describe('FixedChargeComponent', () => {
  let component: FixedChargeComponent;
  let fixture: ComponentFixture<FixedChargeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FixedChargeComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(FixedChargeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
