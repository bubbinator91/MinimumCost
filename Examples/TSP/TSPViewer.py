#! /usr/bin/python

from Tkinter import *
import json
import os.path
import subprocess

class TSPViewer(Frame):
	locations = None

	def __init__(self, parent):
		Frame.__init__(self, parent)
		self.parent = parent
		self.initUI()

	def initUI(self):
		self.parent.title("TSP Viewer")
		self.pack(fill = BOTH, expand = 1)

		canvas = Canvas(self)

		if (not os.path.isfile('location_data.json')):
			print 'Could not find location data!\n'
			quit()
		else:
			location_data_json_file = open('location_data.json', 'r')
			location_data_json_string = location_data_json_file.read()
			location_data_json_file.close()
			location_data = json.loads(location_data_json_string)
			self.locations = location_data['locations']

			for location in self.locations:
				x = location['x'];
				y = location['y'];
				canvas.create_oval(x, y, x + 6, y + 6, outline = "black", fill = "black")

			if (not os.path.isfile('route.txt')):
				print 'Could not find route data!\n'
				quit()
			else:
				route_data_file = open('route.txt', 'r')
				
				route = []
				for line in route_data_file:
					route.append(int(line.rstrip('\r\n')))
				route_data_file.close

				from_id = route[0]
				for i in range(1, len(route)):
					to_id = route[i]
					canvas.create_line(self.getLocation(from_id)['x'] + 3, self.getLocation(from_id)['y'] + 3, self.getLocation(to_id)['x'] + 3, self.getLocation(to_id)['y'] + 3)
					from_id = to_id

				from_id = route[len(route) - 1]
				to_id = route[0]
				canvas.create_line(self.getLocation(from_id)['x'] + 3, self.getLocation(from_id)['y'] + 3, self.getLocation(to_id)['x'] + 3, self.getLocation(to_id)['y'] + 3)

		canvas.pack(fill = BOTH, expand = 1)

	def getLocation(self, id):
		for location in self.locations:
			if (location["id"] == id):
				return location
		return None

def hasPHP():
	try:
		subprocess.check_call(['php', '-v'], stdout = subprocess.PIPE)
		return True
	except:
		return False

def main():
	if (not os.path.isfile('location_data.json') or not os.path.isfile('route.txt')):
		print 'No location data found. Attempting to generate location data.'
		if (not hasPHP()):
			print 'You do not have PHP installed. It needs to be installed to continue.'
			quit()
		elif (not os.path.isfile('TSP.php')):
			print 'TSP.php not found. Cannot continue.'
			quit()

		if (subprocess.call(['php', 'TSP.php']) != 0):
			print 'There was an error during the generation of location data.'
			quit()

	root = Tk()
	tsp_viewer = TSPViewer(root)
	root.geometry("500x500")
	root.mainloop()

if __name__ == '__main__':
	main()