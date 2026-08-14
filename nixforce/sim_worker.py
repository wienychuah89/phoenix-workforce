# C:\xampp\htdocs\payrollsys\sim_worker.py
import sys
import io

# 强制将标准输出和错误输出改为 UTF-8 编码，防止 Windows 控制台/PHP 管道编码报错
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

import calendar
import random
import sys
import time
from datetime import datetime, timedelta
import requests

API_URL = "http://localhost:8081/payrollsys/upload.php"


def generate_random_time(date_obj, start_hour, end_hour, peak_hour=None):
    start_sec = int(start_hour * 3600)
    end_sec = int(end_hour * 3600)

    if peak_hour is not None:
        peak_sec = int(peak_hour * 3600)
        sigma = (end_sec - start_sec) / 6
        chosen_sec = int(random.gauss(peak_sec, sigma))
        chosen_sec = max(start_sec, min(chosen_sec, end_sec))
    else:
        chosen_sec = random.randint(start_sec, end_sec)

    return datetime.combine(date_obj, datetime.min.time()) + timedelta(
        seconds=chosen_sec
    )


def run_simulation(card_id: str, year: int, month: int):
    session = requests.Session()
    _, num_days = calendar.monthrange(year, month)

    print(
        f"开始处理: 卡号 [{card_id}] | 目标月份: {year}-{month:02d} | 总天数: {num_days}天"
    )

    total_logs = 0
    for day in range(1, num_days + 1):
        current_date = datetime(year, month, day).date()

        # 跳过周末 (5=周六, 6=周日)
        if current_date.weekday() >= 5:
            continue

        # 1. 早上上班 7:00 - 8:30 (峰值 7:55 / 7.92小时)
        time_in = generate_random_time(
            current_date, 7.0, 8.5, peak_hour=7.92
        ).strftime("%Y-%m-%d %H:%M:%S")

        # 2. 午休外出 12:00 - 12:30 (峰值 12:05 / 12.08小时)
        lunch_out = generate_random_time(
            current_date, 12.0, 12.5, peak_hour=12.08
        ).strftime("%Y-%m-%d %H:%M:%S")

        # 3. 午休返回 13:00 - 14:00 (峰值 13:25 / 13.41小时)
        lunch_in = generate_random_time(
            current_date, 13.0, 14.0, peak_hour=13.41
        ).strftime("%Y-%m-%d %H:%M:%S")

        # 4. 晚上下班 17:00 - 20:00 (峰值 17:15 / 17.25小时)
        time_out = generate_random_time(
            current_date, 17.0, 20.0, peak_hour=17.25
        ).strftime("%Y-%m-%d %H:%M:%S")

        # 一天依次推送 4 次打卡记录
        for swipe_time in [time_in, lunch_out, lunch_in, time_out]:
            payload = {"card_uid": card_id, "created_at": swipe_time}
            try:
                res = session.post(API_URL, data=payload, timeout=5)
                if res.status_code == 200:
                    total_logs += 1
            except Exception as e:
                print(f"Error: {e}")

            time.sleep(0.01)

    print(
        f"处理完成！成功生成并写入 {total_logs} 条打卡记录 (已排除周末)。"
    )


if __name__ == "__main__":
    # 从命令行获取参数: python sim_worker.py <card_id> <year> <month>
    if len(sys.argv) >= 4:
        card_id_input = sys.argv[1]
        year_input = int(sys.argv[2])
        month_input = int(sys.argv[3])

        run_simulation(card_id_input, year_input, month_input)
    else:
        print("错误：未提供足够的参数。正确格式: python sim_worker.py <卡号> <年份> <月份>")