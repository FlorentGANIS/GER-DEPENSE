import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TopbarComponent } from './topbar.component';
import { AuthfakeauthenticationService } from 'src/app/core/services/authfake.service';
import { AuthenticationService } from 'src/app/core/services/auth.service';
import { HttpClientTestingModule } from '@angular/common/http/testing';

describe('TopbarComponent', () => {
  let component: TopbarComponent;
  let fixture: ComponentFixture<TopbarComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [TopbarComponent],
      providers: [AuthfakeauthenticationService, AuthenticationService],
      imports: [HttpClientTestingModule]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TopbarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
