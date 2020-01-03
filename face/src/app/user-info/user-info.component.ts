import {Component, OnInit, ViewChild} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';
import {Config} from '../config';

@Component({
  selector: 'app-user-info',
  templateUrl: './user-info.component.html',
  styleUrls: ['./user-info.component.css']
})

export class UserInfoComponent implements OnInit {
  config = new Config();
  employees = {};
  user;
  current;
  selected;
  uploadMessage = '';
  oldPasswordError = '';
  passwordError = '';
  repeatPasswordError = '';

  constructor(private http: HttpClient, private cookieService: CookieService) {
    this.http.get(this.config.url + 'users').subscribe( data => {
      this.employees = (data as any).employees;
    });
    if (cookieService.check('session')) {
      this.user = JSON.parse(cookieService.get('user'));
      this.load(this.user.id);
    }
  }

  ngOnInit() {}

  load(id) {
    this.http.get(this.config.url + 'users/' + id).pipe().subscribe( data => {
      this.current = (data as any).employee;
      this.selected = this.current.id;
    });
  }

  update(passwordOld, password, passwordRepeat, department, admin) {
    passwordOld = passwordOld === undefined ? '' : passwordOld.value;
    department = department === undefined ? this.current.department : department.selectedOptions[0].value;
    admin = admin === undefined ? this.current.admin : admin.checked;
    this.uploadMessage = '';
    this.oldPasswordError = '';
    this.passwordError = '';
    this.repeatPasswordError = '';

    this.http.patch(this.config.url + 'users/' + this.current.id, {
      editor_admin: this.user.admin,
      password_old: passwordOld,
      password,
      password_repeat: passwordRepeat,
      department,
      admin
    }).subscribe( data => {
      console.log(data);
      if ((data as any).success) {
        this.load(this.current.id);
        this.uploadMessage = 'Update succeeded!';
      } else {
        this.oldPasswordError = (data as any).oldPasswordError;
        this.passwordError = (data as any).passwordError;
        this.repeatPasswordError = (data as any).repeatPasswordError;
      }
    });
  }
}
