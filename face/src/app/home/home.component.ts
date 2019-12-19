import {Component, OnInit} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {
  employees;
  user;
  today = new Date();
  birthday;

  constructor(private http: HttpClient, private cookieService: CookieService) {
    http.get('http://localhost:8000/birthdays').subscribe( data => {
      this.employees = (data as any).employees;
      let i;
      for (i = 0; i < this.employees.length; i++) {
          this.employees[i].birthday = new Date(this.employees[i].birthday).getDate();
      }
    });
    if (cookieService.check('user')) {
        this.user = JSON.parse(cookieService.get('user'));
        this.birthday = new Date(this.user.birthday);
    }
  }

  ngOnInit() {}
}
