#!/bin/sh
echo 'すべてソースコードをアップ'

rsync -auvz --exclude='.env' ../dev amaraimusi@amaraimusi.sakura.ne.jp:www/mng/minisched/

rsync -auvz  ../dev/.env_p amaraimusi@amaraimusi.sakura.ne.jp:www/mng/minisched/dev/.env

echo "------------ アップロード完了"
#cmd /k