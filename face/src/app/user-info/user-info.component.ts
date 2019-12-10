import {Component, OnInit, ViewChild} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {CookieService} from 'ngx-cookie-service';

@Component({
  selector: 'app-user-info',
  templateUrl: './user-info.component.html',
  styleUrls: ['./user-info.component.css']
})

export class UserInfoComponent implements OnInit {
  employees = {};
  user;
  current = {
    email: '',
    name : '',
    department: '',
    admin: false
  };
  selected;
  uploadMessage = '';
  oldPasswordError = '';
  passwordError = '';
  repeatPasswordError = '';

  constructor(private http: HttpClient, private cookieService: CookieService) {
    this.http.get('http://localhost:8000/users').subscribe( data => {
      this.employees = (data as any).employees;
    });
    if (cookieService.check('session')) {
      this.user = JSON.parse(cookieService.get('user'));
      this.load(this.user.email);
    }
  }

  ngOnInit() {}

  load(email) {
    this.http.post('http://localhost:8000/email', {email}).pipe().subscribe( data => {
      this.current.email = (data as any).email;
      this.current.name = (data as any).name;
      this.current.department = (data as any).department;
      this.current.admin = (data as any).admin === 1;
      this.selected = this.current.email;
    });
  }

  update(passwordOld, password, passwordRepeat, department, admin) {
    const old = passwordOld === undefined ? '' : passwordOld.value;
    const dprtmnt = department === undefined ? this.current.department : department.selectedOptions[0].value;
    const admn = admin === undefined ? this.current.admin : admin.checked;
    this.uploadMessage = '';
    this.oldPasswordError = '';
    this.passwordError = '';
    this.repeatPasswordError = '';

    this.http.post('http://localhost:8000/update', {
      editor_admin: this.user.admin,
      email: this.current.email,
      password_old: old,
      password,
      password_repeat: passwordRepeat,
      department: dprtmnt,
      admin: admn
    }).pipe().subscribe( data => {
      console.log(data);
      if ((data as any).success) {
        this.uploadMessage = 'Update succeeded!';
      } else {
        this.oldPasswordError = (data as any).old_password_error;
        this.passwordError = (data as any).password_error;
        this.repeatPasswordError = (data as any).repeat_password_error;
      }
    });
  }
}
