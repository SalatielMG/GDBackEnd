select t.*, count(t.id_backup) as CantRep from ((
select * from full_backup_accounts union all select * from backup_accounts
) as t) group by
t.id_backup,t.id_account,t.NAME,t.detail,t.SIGN,t.income,t.expense,t.initial_balance,t.final_balance,t.MONTH,t.YEAR,t.positive_limit,t.negative_limit,t.positive_max,t.negative_max,t.iso_code,t.selected,t.value_type,t.include_total,t.rate,t.icon_name order by CantRep