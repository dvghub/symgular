import {Component, Input, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-nav-bar',
  templateUrl: './nav-bar.component.html',
  styleUrls: ['./nav-bar.component.css']
})
export class NavBarComponent implements OnInit {
  @Input()
  active;
  @Input()
  user;

  itemClass = 'nav-item nav-link text-decoration-none px-3 py-2 my-auto';
  loginClass = this.active === 'login' ? 'active' : '';
  newClass = this.active === 'new' ? 'active' : '';
  logoutClass = this.active === 'new' ? 'active' : '';
  userClass = this.active === 'user' ? 'active' : '';
  leaveClass = this.active === 'leave' ? 'active' : '';

  constructor(private http: HttpClient, private cookieService: CookieService) {}

  ngOnInit() {}

  logout() {
    this.http.get('http://locahost:8000/logout');
    this.cookieService.deleteAll();
    window.location.href = '/';
  }
}
