import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})

export class AppComponent implements OnInit {
    href;
    active;
    user = {
        id: null,
        firstName: '',
        lastName: '',
        email: '',
        department: '',
        birthday: '',
        admin: false
    };

  constructor(private http: HttpClient, private cookieService: CookieService) {
      if (this.cookieService.check('user')) {
          this.user = JSON.parse(cookieService.get('user'));
      }
  }

  ngOnInit() {
      this.href = window.location.href.split('/');
      this.active = this.href[3]; // Third is always active page
  }
}
