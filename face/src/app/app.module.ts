import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';

import { AppComponent } from './app.component';
import { NavBarComponent } from './nav-bar/nav-bar.component';
import { LoginFormComponent } from './login-form/login-form.component';
import { FooterBarComponent } from './footer-bar/footer-bar.component';
import { HomeComponent } from './home/home.component';
import { NewUserComponent } from './new-user/new-user.component';
import { CookieService } from 'ngx-cookie-service';
import { UserInfoComponent } from './user-info/user-info.component';
import { LeaveComponent } from './leave/leave.component';
import { LeaveReviewComponent } from './leave-review/leave-review.component';
import { LeaveEditComponent } from './leave-edit/leave-edit.component';

@NgModule({
  declarations: [
    AppComponent,
    LoginFormComponent,
    NavBarComponent,
    FooterBarComponent,
    HomeComponent,
    NewUserComponent,
    UserInfoComponent,
    LeaveComponent,
    LeaveReviewComponent,
    LeaveEditComponent
  ],
  imports: [
    BrowserModule,
    RouterModule.forRoot([
        {path: '', component: HomeComponent},
        {path: 'home', component: HomeComponent},
        {path: 'login', component: LoginFormComponent},
        {path: 'new', component: NewUserComponent},
        {path: 'user', component: UserInfoComponent},
        {path: 'leave', component: LeaveComponent},
        {path: 'review', component: LeaveReviewComponent}
    ]),
    HttpClientModule
  ],
  providers: [CookieService],
  bootstrap: [AppComponent]
})
export class AppModule { }
