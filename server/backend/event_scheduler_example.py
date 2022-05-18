#!/usr/bin/python3
import sched
import threading
import time
from _thread import start_new_thread

scheduler = sched.scheduler(time.time, time.sleep)

# Set up a global to be modified by the threads
counter = 0


def schedule(name):
	start_new_thread(increment_counter,(name,))

def increment_counter(name):
	global counter
	print('EVENT:', time.time(), name)
	time.sleep(2)
	counter += 1
	print('NOW:', counter)
	print('START:', time.time())

now = time.time()
print(now)
e1 = scheduler.enterabs(now - 2, 1, schedule, ('E1',))
e2 = scheduler.enterabs(now - 3, 1, schedule, ('E2',))
e3 = scheduler.enterabs(now - 3, 1, schedule, ('E3',))

print(e1)
print(e2)
print(e3)
# Start a thread to run the events
# t = threading.Thread(target=scheduler.run)
# t.start()
start_new_thread(scheduler.run,())
# scheduler.run()
# scheduler.run()
# Back in the main thread, cancel the first scheduled event.
scheduler.cancel(e1)
e4 = scheduler.enterabs(now - 3, 1, schedule, ('E4',))
# Wait for the scheduler to finish running in the thread
# t.join()

print('FINAL:', counter)
while(True):
	time.sleep(1)