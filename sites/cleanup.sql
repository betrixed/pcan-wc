delete IGNORE from blog_to_category  where blog_id in 
(select id from blog where date_published < '2018-06-01')

Select * from blog_to_category BC  where BC.blog_id in 
(select id from blog where date_published < '2018-06-01')

delete  from blog where date_published < '2018-06-01'

delete  from links where date_created < '2018-06-01'

delete from user_event where user_id > 1
delete from user_auth where userId > 1
delete from users where id > 1